package proxy

import (
	"context"
	"errors"
	"fmt"
	"log"
	"sync"
	"time"

	"github.com/arguspam/iris/pool"
	"github.com/arguspam/iris/shipper"
)

var ErrSessionNotFound = errors.New("session not found")

// StartRequest carries the parameters for starting a new proxy session.
type StartRequest struct {
	SessionID   string
	DBMS        string
	JitUsername string
	JitPassword string
	DBHost      string
	DBPort      int
}

// StatusResponse is the response for GET /proxy/{id}.
type StatusResponse struct {
	Active           bool      `json:"active"`
	Port             int       `json:"port"`
	QueryCount       int64     `json:"query_count"`
	ClientConnected  bool      `json:"client_connected"`
	SessionStartedAt time.Time `json:"session_started_at"`
}

// StopResponse is the response for DELETE /proxy/{id}.
// Flushed is always true: stop() always attempts a final flush; any delivery
// failures are logged by the shipper but do not block session teardown.
type StopResponse struct {
	Flushed      bool  `json:"flushed"`
	TotalQueries int64 `json:"total_queries"`
}

// slot holds the state for a session entry in the manager map.
// It is created as an empty sentinel when Start() reserves a slot, then
// populated with port and sess as Start() progresses. Stop() consults the
// slot's fields to clean up whatever was acquired before it was called.
type slot struct {
	sess *Session // nil until Start() promotes the session
	port int      // 0 until the port is acquired
}

// Manager owns the session registry and port pool.
type Manager struct {
	pool           *pool.Pool
	argusURL       string
	secret         string
	bindAddr       string
	flushInterval  time.Duration
	flushBatchSize int

	mu       sync.Mutex
	sessions map[string]*slot
}

func NewManager(p *pool.Pool, argusURL, secret, bindAddr string, flushInterval time.Duration, flushBatchSize int) *Manager {
	return &Manager{
		pool:           p,
		argusURL:       argusURL,
		secret:         secret,
		bindAddr:       bindAddr,
		flushInterval:  flushInterval,
		flushBatchSize: flushBatchSize,
		sessions:       make(map[string]*slot),
	}
}

// Start allocates a port, creates a session, and starts the listener.
// Returns the allocated port on success.
//
// Concurrency: a nil-sess slot is inserted under lock to atomically reserve the
// ID without holding the lock during expensive operations. The port is recorded
// in the slot (also under lock) as soon as it is acquired so that a concurrent
// Stop() can always release it, regardless of how far Start() has progressed.
func (m *Manager) Start(req StartRequest) (int, error) {
	m.mu.Lock()
	if _, exists := m.sessions[req.SessionID]; exists {
		m.mu.Unlock()
		return 0, fmt.Errorf("session %s already exists", req.SessionID)
	}
	sl := &slot{}
	m.sessions[req.SessionID] = sl
	m.mu.Unlock()

	port, err := m.pool.Acquire(req.SessionID)
	if err != nil {
		m.mu.Lock()
		if m.sessions[req.SessionID] == sl {
			delete(m.sessions, req.SessionID)
		}
		m.mu.Unlock()
		return 0, err
	}

	// Record the port in the slot before releasing the lock so that a concurrent
	// Stop() can release the port even if we haven't promoted yet.
	m.mu.Lock()
	if m.sessions[req.SessionID] != sl {
		// Stop() deleted our slot between Acquire and here. Clean up.
		m.mu.Unlock()
		m.pool.ReleaseFor(port, req.SessionID)
		return 0, fmt.Errorf("session %s was cancelled by a concurrent stop", req.SessionID)
	}
	sl.port = port
	m.mu.Unlock()

	ship := shipper.New(req.SessionID, m.argusURL, m.secret, m.flushInterval, m.flushBatchSize)
	sess := newSession(req.SessionID, port, req.DBMS, req.JitUsername, req.JitPassword, req.DBHost, req.DBPort, m.bindAddr, ship)

	if err := sess.start(); err != nil {
		m.mu.Lock()
		if m.sessions[req.SessionID] == sl {
			delete(m.sessions, req.SessionID)
		}
		m.mu.Unlock()
		m.pool.ReleaseFor(port, req.SessionID)
		return 0, err
	}

	// Promote slot to the real session.
	m.mu.Lock()
	if m.sessions[req.SessionID] != sl {
		// Stop() deleted our slot during sess.start(). Tear down and abort.
		m.mu.Unlock()
		sess.stop()
		m.pool.ReleaseFor(port, req.SessionID)
		return 0, fmt.Errorf("session %s was cancelled by a concurrent stop", req.SessionID)
	}
	sl.sess = sess
	m.mu.Unlock()

	return port, nil
}

// Stop flushes and tears down a session, returning the total query count.
func (m *Manager) Stop(sessionID string) (StopResponse, error) {
	m.mu.Lock()
	sl, ok := m.sessions[sessionID]
	if ok {
		delete(m.sessions, sessionID)
	}
	m.mu.Unlock()

	if !ok {
		return StopResponse{}, ErrSessionNotFound
	}
	// Release port if acquired (sl.port == 0 means Start() hadn't acquired yet).
	if sl.port != 0 {
		m.pool.ReleaseFor(sl.port, sessionID)
	}
	// Stop session if it was promoted (sl.sess == nil means not promoted yet).
	if sl.sess == nil {
		return StopResponse{Flushed: true, TotalQueries: 0}, nil
	}
	sl.sess.stop()
	return StopResponse{Flushed: true, TotalQueries: sl.sess.QueryCount()}, nil
}

// Status returns the current state of a session.
func (m *Manager) Status(sessionID string) (StatusResponse, error) {
	m.mu.Lock()
	sl, ok := m.sessions[sessionID]
	m.mu.Unlock()

	if !ok {
		return StatusResponse{}, ErrSessionNotFound
	}
	if sl.sess == nil {
		return StatusResponse{Active: false}, nil
	}
	return StatusResponse{
		Active:           true,
		Port:             sl.port,
		QueryCount:       sl.sess.QueryCount(),
		ClientConnected:  sl.sess.IsClientConnected(),
		SessionStartedAt: sl.sess.StartedAt,
	}, nil
}

// ActiveSessionCount returns the number of fully started (promoted) sessions.
// Slots reserved by Start() but not yet promoted are excluded.
func (m *Manager) ActiveSessionCount() int {
	m.mu.Lock()
	defer m.mu.Unlock()
	count := 0
	for _, sl := range m.sessions {
		if sl.sess != nil {
			count++
		}
	}
	return count
}

// StopAll stops every active session and releases their ports.
// Sessions are stopped concurrently to bound total teardown time.
// ctx is used as a deadline: if it expires before all sessions finish,
// StopAll returns immediately (the goroutines continue running until the
// process exits, which the orchestrator will enforce via SIGKILL).
func (m *Manager) StopAll(ctx context.Context) {
	m.mu.Lock()
	slots := make(map[string]*slot, len(m.sessions))
	for id, sl := range m.sessions {
		slots[id] = sl
	}
	m.sessions = make(map[string]*slot)
	m.mu.Unlock()

	var wg sync.WaitGroup
	for id, sl := range slots {
		wg.Add(1)
		go func(id string, sl *slot) {
			defer wg.Done()
			if sl.sess != nil {
				sl.sess.stop()
			}
			if sl.port != 0 {
				m.pool.ReleaseFor(sl.port, id)
			}
		}(id, sl)
	}

	stopped := make(chan struct{})
	go func() {
		wg.Wait()
		close(stopped)
	}()

	select {
	case <-stopped:
	case <-ctx.Done():
		log.Printf("[manager] StopAll deadline exceeded; some sessions may not have flushed cleanly")
	}
}
