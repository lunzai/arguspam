package shipper

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"net/url"
	"sync"
	"time"
)

// QueryLog is a single intercepted query event sent to the Laravel API.
type QueryLog struct {
	Timestamp    time.Time `json:"timestamp"`
	Query        string    `json:"query"`
	DurationMs   int64     `json:"duration_ms"`
	RowsAffected int64     `json:"rows_affected"`
	Error        string    `json:"error,omitempty"`
}

type payload struct {
	Queries []QueryLog `json:"queries"`
}

const (
	maxRetries    = 3
	retryBaseWait = 500 * time.Millisecond

	// sendTimeout is the per-attempt HTTP deadline for normal (non-shutdown) sends.
	sendTimeout = 10 * time.Second
	// shutdownSendTimeout bounds the single best-effort flush performed at shutdown.
	// Kept short so session teardown does not stall the container orchestrator.
	shutdownSendTimeout = 5 * time.Second
)

// Shipper buffers query logs and delivers them in-order to the Laravel API.
//
// Design: a single sender goroutine owns all HTTP sends so batches are always
// delivered in the order they were flushed, with no concurrent sends.
// Add() appends to an internal buffer and signals the sender when the batch
// threshold is reached; the ticker also signals the sender on each tick.
type Shipper struct {
	sessionID string
	apiURL    string
	secret    string
	batchSize int
	interval  time.Duration
	client    *http.Client

	mu       sync.Mutex
	buffer   []QueryLog
	flushCh  chan struct{} // signals sender to flush now
	done     chan struct{}
	wg       sync.WaitGroup
	stopOnce sync.Once
}

func New(sessionID, apiURL, secret string, interval time.Duration, batchSize int) *Shipper {
	return &Shipper{
		sessionID: sessionID,
		apiURL:    apiURL,
		secret:    secret,
		batchSize: batchSize,
		interval:  interval,
		// No client-level Timeout: each request carries its own context deadline,
		// which is more precise. A generous cap is kept as a safety net.
		client:  &http.Client{Timeout: 30 * time.Second},
		flushCh: make(chan struct{}, 1), // buffered: signal without blocking
		done:    make(chan struct{}),
	}
}

// Start launches the sender goroutine.
func (s *Shipper) Start() {
	s.wg.Add(1)
	go s.sender()
}

// Add appends a query log entry. If the buffer reaches batchSize it signals
// the sender goroutine to flush immediately.
func (s *Shipper) Add(q QueryLog) {
	s.mu.Lock()
	s.buffer = append(s.buffer, q)
	shouldFlush := len(s.buffer) >= s.batchSize
	s.mu.Unlock()

	if shouldFlush {
		s.signal()
	}
}

// Stop shuts down the sender goroutine and performs a final flush.
// Safe to call multiple times; subsequent calls are no-ops.
func (s *Shipper) Stop() {
	s.stopOnce.Do(func() {
		close(s.done)
		s.wg.Wait()
	})
}

// signal wakes the sender without blocking. If a signal is already pending
// the buffered channel absorbs this one so no flush is skipped.
func (s *Shipper) signal() {
	select {
	case s.flushCh <- struct{}{}:
	default:
	}
}

// sender is the single goroutine responsible for all HTTP sends.
// This ensures batches are delivered strictly in order.
func (s *Shipper) sender() {
	defer s.wg.Done()
	ticker := time.NewTicker(s.interval)
	defer ticker.Stop()

	for {
		select {
		case <-ticker.C:
			s.drainAndSend()
		case <-s.flushCh:
			s.drainAndSend()
		case <-s.done:
			s.drainAndSendFinal() // bounded, best-effort shutdown flush
			return
		}
	}
}

func (s *Shipper) drainAndSend() {
	s.mu.Lock()
	if len(s.buffer) == 0 {
		s.mu.Unlock()
		return
	}
	batch := s.buffer
	s.buffer = nil
	s.mu.Unlock()

	s.sendWithRetry(batch)
}

// drainAndSendFinal performs the shutdown flush with a short bounded deadline.
// It does not retry — the goal is a single best-effort delivery so that
// session teardown does not stall the container orchestrator.
func (s *Shipper) drainAndSendFinal() {
	s.mu.Lock()
	if len(s.buffer) == 0 {
		s.mu.Unlock()
		return
	}
	batch := s.buffer
	s.buffer = nil
	s.mu.Unlock()

	ctx, cancel := context.WithTimeout(context.Background(), shutdownSendTimeout)
	defer cancel()
	if err := s.send(ctx, batch); err != nil {
		log.Printf("[shipper] session=%s FLUSH FAILURE on shutdown: %v — %d queries lost",
			s.sessionID, err, len(batch))
	}
}

// sendWithRetry attempts to deliver a batch up to maxRetries times with
// exponential back-off. Each attempt uses an independent per-request context
// so a slow API does not carry over to subsequent attempts.
// The back-off sleep is interruptible via s.done so that shutdown is never
// delayed by in-flight retries.
// Permanent failure is loudly logged so operators know audit data was not delivered.
func (s *Shipper) sendWithRetry(batch []QueryLog) {
	var lastErr error
	wait := retryBaseWait
	for attempt := 1; attempt <= maxRetries; attempt++ {
		ctx, cancel := context.WithTimeout(context.Background(), sendTimeout)
		err := s.send(ctx, batch)
		cancel()
		if err == nil {
			return
		}
		lastErr = err
		if attempt < maxRetries {
			log.Printf("[shipper] session=%s send attempt %d/%d failed: %v — retrying in %s",
				s.sessionID, attempt, maxRetries, lastErr, wait)
			select {
			case <-time.After(wait):
			case <-s.done:
				// Shutting down — one final bounded attempt, no further retries.
				ctx2, cancel2 := context.WithTimeout(context.Background(), shutdownSendTimeout)
				if finalErr := s.send(ctx2, batch); finalErr != nil {
					log.Printf("[shipper] session=%s FLUSH FAILURE on shutdown: %v — %d queries lost",
						s.sessionID, finalErr, len(batch))
				}
				cancel2()
				return
			}
			wait *= 2
		}
	}
	log.Printf("[shipper] session=%s PERMANENT FLUSH FAILURE after %d attempts: %v — %d queries lost",
		s.sessionID, maxRetries, lastErr, len(batch))
}

func (s *Shipper) send(ctx context.Context, queries []QueryLog) error {
	body, err := json.Marshal(payload{Queries: queries})
	if err != nil {
		return fmt.Errorf("marshal: %w", err)
	}

	endpoint := fmt.Sprintf("%s/internal/proxy/sessions/%s/logs", s.apiURL, url.PathEscape(s.sessionID))
	req, err := http.NewRequestWithContext(ctx, http.MethodPost, endpoint, bytes.NewReader(body))
	if err != nil {
		return fmt.Errorf("build request: %w", err)
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("X-Proxy-Secret", s.secret)

	resp, err := s.client.Do(req)
	if err != nil {
		return fmt.Errorf("http: %w", err)
	}
	defer func() {
		_, _ = io.Copy(io.Discard, resp.Body)
		resp.Body.Close()
	}()

	if resp.StatusCode >= 400 {
		return fmt.Errorf("api returned %d", resp.StatusCode)
	}
	return nil
}
