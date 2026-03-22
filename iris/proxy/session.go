package proxy

import (
	"context"
	"errors"
	"fmt"
	"io"
	"log"
	"net"
	"strings"
	"sync"
	"sync/atomic"
	"syscall"
	"time"

	proxymysql "github.com/arguspam/iris/proxy/mysql"
	"github.com/arguspam/iris/shipper"
	"github.com/go-mysql-org/go-mysql/server"
)

// Session represents a single PAM proxy session.
// It owns a TCP listener on the allocated port, accepts at most one client
// connection at a time (PAM sessions are single-user), and maintains a
// Shipper that batches query logs back to the Laravel API.
type Session struct {
	ID          string
	Port        int
	DBMS        string
	JitUsername string
	JitPassword string
	DBHost      string
	DBPort      int
	BindAddr    string
	StartedAt   time.Time

	shipper     *shipper.Shipper
	listener    net.Listener
	queryCount  atomic.Int64
	clientMu    sync.Mutex
	clientConn  net.Conn // current raw TCP connection from SQL client
	stopped     chan struct{}
	stopOnce    sync.Once
	loopWG      sync.WaitGroup // tracks the acceptLoop goroutine
}

func newSession(
	id string,
	port int,
	dbms, jitUsername, jitPassword, dbHost string,
	dbPort int,
	bindAddr string,
	ship *shipper.Shipper,
) *Session {
	return &Session{
		ID:          id,
		Port:        port,
		DBMS:        dbms,
		JitUsername: jitUsername,
		JitPassword: jitPassword,
		DBHost:      dbHost,
		DBPort:      dbPort,
		BindAddr:    bindAddr,
		StartedAt:   time.Now(),
		shipper:     ship,
		stopped:     make(chan struct{}),
	}
}

// start opens the listener and launches the accept loop.
func (s *Session) start() error {
	ln, err := net.Listen("tcp", fmt.Sprintf("%s:%d", s.BindAddr, s.Port))
	if err != nil {
		return fmt.Errorf("listen on port %d: %w", s.Port, err)
	}
	s.listener = ln
	s.shipper.Start()
	s.loopWG.Add(1)
	go func() {
		defer s.loopWG.Done()
		s.acceptLoop()
	}()
	return nil
}

// stop closes the listener, drops the active client connection, waits for the
// accept loop goroutine to exit, then performs the final shipper flush.
// QueryCount() can be read after stop() returns for the total query tally.
func (s *Session) stop() {
	s.stopOnce.Do(func() {
		close(s.stopped)
		_ = s.listener.Close()

		s.clientMu.Lock()
		if s.clientConn != nil {
			_ = s.clientConn.Close()
		}
		s.clientMu.Unlock()

		// Wait for the accept loop goroutine to exit before flushing so that
		// no Add() calls can race the shipper shutdown.
		s.loopWG.Wait()
		s.shipper.Stop()
	})
}

// QueryCount returns the number of queries logged so far.
func (s *Session) QueryCount() int64 {
	return s.queryCount.Load()
}

// IsClientConnected reports whether a SQL client is currently connected.
func (s *Session) IsClientConnected() bool {
	s.clientMu.Lock()
	defer s.clientMu.Unlock()
	return s.clientConn != nil
}

// acceptLoop accepts one TCP connection at a time and hands it to handleConn.
// If the client disconnects, it waits for the next connection on the same port
// (the session window is still open).
// Transient accept errors (e.g. EMFILE) are retried with a short back-off;
// fatal errors (listener closed) cause the loop to exit cleanly.
func (s *Session) acceptLoop() {
	for {
		tcpConn, err := s.listener.Accept()
		if err != nil {
			// Listener was closed as part of normal shutdown.
			select {
			case <-s.stopped:
				return
			default:
			}

			// Transient OS errors (e.g. EMFILE, ENFILE): sleep briefly and retry
			// so a resource spike does not permanently kill this session.
			if isTransientAcceptErr(err) {
				log.Printf("[session %s] transient accept error, retrying: %v", s.ID, err)
				time.Sleep(100 * time.Millisecond)
				continue
			}

			log.Printf("[session %s] accept error (fatal): %v", s.ID, err)
			return
		}

		// Guard against a race where stop() closed the listener but a connection
		// slipped through Accept() before the close took effect. If we are already
		// stopped, discard the connection rather than orphaning it.
		s.clientMu.Lock()
		select {
		case <-s.stopped:
			s.clientMu.Unlock()
			tcpConn.Close()
			continue
		default:
		}
		s.clientConn = tcpConn
		s.clientMu.Unlock()

		// Ensure clientConn is cleared even if handleConn panics.
		// On panic, handleConn's own defer tcpConn.Close() runs first as the
		// stack unwinds, but we close it here too as an explicit safety net.
		func() {
			defer func() {
				if r := recover(); r != nil {
					log.Printf("[session %s] handleConn panic: %v", s.ID, r)
					tcpConn.Close() // idempotent; guards against any future refactor
				}
				s.clientMu.Lock()
				s.clientConn = nil
				s.clientMu.Unlock()
			}()
			s.handleConn(tcpConn)
		}()
	}
}

// handleConn proxies a single client TCP connection.
func (s *Session) handleConn(tcpConn net.Conn) {
	defer tcpConn.Close()

	if s.DBMS != "mysql" {
		log.Printf("[session %s] unsupported dbms: %s", s.ID, s.DBMS)
		return
	}

	// Open upstream connection to the real database.
	// The dial context is derived from s.stopped so that a session stop
	// immediately cancels an in-progress dial rather than blocking for the
	// full dialTimeout. The goroutine exits as soon as either fires.
	// Log only the session ID on failure — never log the upstream address.
	dialCtx, cancelDial := context.WithCancel(context.Background())
	go func() {
		select {
		case <-s.stopped:
			cancelDial()
		case <-dialCtx.Done():
		}
	}()
	defer cancelDial()

	upstream, err := proxymysql.Dial(dialCtx, s.DBHost, s.DBPort, s.JitUsername, s.JitPassword)
	if err != nil {
		log.Printf("[session %s] upstream dial failed", s.ID)
		return
	}
	defer upstream.Close()

	// Wrap upstream in a query-counting handler.
	logAdder := &countingLogAdder{shipper: s.shipper, counter: &s.queryCount}
	handler := proxymysql.New(upstream, logAdder)

	// Create the server-side MySQL connection. The client must authenticate
	// using the JIT credentials (same credentials the upstream uses).
	srvConn, err := server.NewConn(tcpConn, s.JitUsername, s.JitPassword, handler)
	if err != nil {
		log.Printf("[session %s] mysql handshake failed: %v", s.ID, err)
		return
	}

	log.Printf("[session %s] client connected", s.ID)
	for {
		select {
		case <-s.stopped:
			return
		default:
		}
		if err := srvConn.HandleCommand(); err != nil {
			if !isDisconnectErr(err) {
				log.Printf("[session %s] command error: %v", s.ID, err)
			}
			return
		}
	}
}

func isDisconnectErr(err error) bool {
	if errors.Is(err, io.EOF) || errors.Is(err, net.ErrClosed) {
		return true
	}
	// go-mysql may wrap syscall errors; also catch hard client disconnects
	// (ECONNRESET = RST packet, EPIPE = broken write pipe).
	var errno syscall.Errno
	if errors.As(err, &errno) {
		return errno == syscall.ECONNRESET || errno == syscall.EPIPE
	}
	// Fallback: match common disconnect message substrings for errors that
	// are not unwrappable to a syscall.Errno (e.g. wrapped by go-mysql's
	// pingcap/errors which does not always preserve the chain).
	msg := err.Error()
	return strings.Contains(msg, "connection reset by peer") ||
		strings.Contains(msg, "broken pipe") ||
		strings.Contains(msg, "use of closed network connection")
}

// isTransientAcceptErr reports whether an Accept() error is transient (e.g.
// EMFILE/ENFILE during a resource spike) and the loop should retry after a
// short sleep. net.Error.Temporary() is deprecated and always false in Go 1.18+
// so we check the underlying syscall.Errno directly.
func isTransientAcceptErr(err error) bool {
	var errno syscall.Errno
	if errors.As(err, &errno) {
		return errno == syscall.EMFILE || errno == syscall.ENFILE || errno == syscall.ENOBUFS
	}
	return false
}

// countingLogAdder wraps the shipper and increments queryCount on every logged query.
type countingLogAdder struct {
	shipper *shipper.Shipper
	counter *atomic.Int64
}

func (c *countingLogAdder) Add(q shipper.QueryLog) {
	c.counter.Add(1)
	c.shipper.Add(q)
}
