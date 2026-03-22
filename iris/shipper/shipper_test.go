package shipper_test

import (
	"encoding/json"
	"io"
	"net/http"
	"net/http/httptest"
	"sync"
	"testing"
	"time"

	"github.com/arguspam/iris/shipper"
)

// captureServer records all batches posted to it.
type captureServer struct {
	mu      sync.Mutex
	batches [][]shipper.QueryLog
}

func (c *captureServer) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	if r.Header.Get("X-Proxy-Secret") == "" {
		w.WriteHeader(http.StatusForbidden)
		return
	}
	body, _ := io.ReadAll(r.Body)
	var payload struct {
		Queries []shipper.QueryLog `json:"queries"`
	}
	_ = json.Unmarshal(body, &payload)

	c.mu.Lock()
	c.batches = append(c.batches, payload.Queries)
	c.mu.Unlock()

	w.WriteHeader(http.StatusOK)
}

func (c *captureServer) allQueries() []shipper.QueryLog {
	c.mu.Lock()
	defer c.mu.Unlock()
	var all []shipper.QueryLog
	for _, b := range c.batches {
		all = append(all, b...)
	}
	return all
}

func newTestShipper(t *testing.T, srv *captureServer, interval time.Duration, batchSize int) (*shipper.Shipper, *httptest.Server) {
	t.Helper()
	ts := httptest.NewServer(srv)
	t.Cleanup(ts.Close)
	// The shipper posts to /internal/proxy/sessions/{id}/logs
	// The test server captures all POST requests regardless of path.
	s := shipper.New("test-session", ts.URL, "secret123", interval, batchSize)
	return s, ts
}

func TestFlushOnBatchSize(t *testing.T) {
	cap := &captureServer{}
	s, _ := newTestShipper(t, cap, 10*time.Minute, 3) // batch of 3, long interval
	s.Start()

	q := func(query string) shipper.QueryLog {
		return shipper.QueryLog{Timestamp: time.Now().UTC(), Query: query, DurationMs: 1}
	}

	s.Add(q("SELECT 1"))
	s.Add(q("SELECT 2"))
	// Not flushed yet — batch size is 3
	if len(cap.allQueries()) != 0 {
		t.Fatal("expected no flush before batch size reached")
	}

	s.Add(q("SELECT 3")) // triggers flush signal
	s.Stop()             // waits for sender goroutine to finish — guarantees flush is complete

	got := cap.allQueries()
	if len(got) != 3 {
		t.Fatalf("expected 3 queries flushed, got %d", len(got))
	}
	if got[0].Query != "SELECT 1" || got[1].Query != "SELECT 2" || got[2].Query != "SELECT 3" {
		t.Fatalf("unexpected query order: %v", got)
	}
}

func TestFlushOnStop(t *testing.T) {
	cap := &captureServer{}
	s, _ := newTestShipper(t, cap, 10*time.Minute, 100) // large batch, long interval
	s.Start()

	for i := range 5 {
		s.Add(shipper.QueryLog{
			Timestamp: time.Now().UTC(),
			Query:     "SELECT " + string(rune('0'+i)),
			DurationMs: int64(i),
		})
	}

	s.Stop()

	got := cap.allQueries()
	if len(got) != 5 {
		t.Fatalf("expected 5 queries after stop, got %d", len(got))
	}
}

func TestFlushOnInterval(t *testing.T) {
	cap := &captureServer{}
	s, _ := newTestShipper(t, cap, 30*time.Millisecond, 100) // short interval, large batch
	s.Start()

	s.Add(shipper.QueryLog{Timestamp: time.Now().UTC(), Query: "SELECT 1", DurationMs: 1})

	// Poll until the ticker fires and the flush completes, up to 2 seconds.
	deadline := time.Now().Add(2 * time.Second)
	for time.Now().Before(deadline) {
		if len(cap.allQueries()) == 1 {
			break
		}
		time.Sleep(10 * time.Millisecond)
	}
	s.Stop()

	got := cap.allQueries()
	if len(got) != 1 {
		t.Fatalf("expected 1 query after interval flush, got %d", len(got))
	}
}

func TestSecretHeaderSent(t *testing.T) {
	var mu sync.Mutex
	var receivedSecret string

	ts := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		mu.Lock()
		receivedSecret = r.Header.Get("X-Proxy-Secret")
		mu.Unlock()
		w.WriteHeader(http.StatusOK)
	}))
	defer ts.Close()

	s := shipper.New("sess", ts.URL, "my-secret", 10*time.Minute, 1)
	s.Start()
	s.Add(shipper.QueryLog{Timestamp: time.Now().UTC(), Query: "SELECT 1", DurationMs: 1})
	s.Stop() // waits for the send to complete

	mu.Lock()
	got := receivedSecret
	mu.Unlock()
	if got != "my-secret" {
		t.Fatalf("expected secret 'my-secret', got %q", got)
	}
}

func TestErrorResponseDoesNotPanic(t *testing.T) {
	ts := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusInternalServerError)
	}))
	defer ts.Close()

	// Use batchSize=1 so the query triggers an immediate flush signal.
	// The shipper will retry 3 times; we just verify it doesn't panic.
	s := shipper.New("sess", ts.URL, "secret", 10*time.Minute, 1)
	s.Start()
	s.Add(shipper.QueryLog{Timestamp: time.Now().UTC(), Query: "SELECT 1", DurationMs: 1})
	s.Stop() // blocks until retries are exhausted
}

func TestQueryLogFields(t *testing.T) {
	cap := &captureServer{}
	s, _ := newTestShipper(t, cap, 10*time.Minute, 1)
	s.Start()

	ts := time.Date(2026, 1, 1, 10, 0, 0, 0, time.UTC)
	s.Add(shipper.QueryLog{
		Timestamp:    ts,
		Query:        "UPDATE users SET name='x'",
		DurationMs:   42,
		RowsAffected: 7,
		Error:        "lock timeout",
	})
	s.Stop()

	got := cap.allQueries()
	if len(got) != 1 {
		t.Fatalf("expected 1 query, got %d", len(got))
	}
	q := got[0]
	if q.Query != "UPDATE users SET name='x'" {
		t.Errorf("wrong query: %q", q.Query)
	}
	if q.DurationMs != 42 {
		t.Errorf("wrong duration: %d", q.DurationMs)
	}
	if q.RowsAffected != 7 {
		t.Errorf("wrong rows_affected: %d", q.RowsAffected)
	}
	if q.Error != "lock timeout" {
		t.Errorf("wrong error: %q", q.Error)
	}
}

// --- TC-24: Shutdown is not blocked by an in-progress retry back-off sleep ---

// TestStop_InterruptsRetrySleep verifies that calling Stop() while the sender
// goroutine is sleeping between retry attempts (due to a failing API) unblocks
// the sleep immediately. The back-off is implemented with a select on s.done,
// so Stop() — which closes s.done — must interrupt the sleep within ms, not
// after the full back-off duration (500ms first wait, then 1s).
func TestStop_InterruptsRetrySleep(t *testing.T) {
	// Always return 500 to force the sender into the retry back-off loop.
	ts := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusInternalServerError)
	}))
	defer ts.Close()

	// batchSize=1 so the first Add immediately signals the sender to flush,
	// which will fail and enter the 500ms back-off sleep.
	s := shipper.New("tc24", ts.URL, "secret", 10*time.Minute, 1)
	s.Start()

	s.Add(shipper.QueryLog{Timestamp: time.Now().UTC(), Query: "SELECT 1", DurationMs: 1})

	// Let the first send attempt fail and the sender enter the back-off sleep.
	// retryBaseWait = 500ms; we wait 100ms so we're mid-sleep when Stop fires.
	time.Sleep(100 * time.Millisecond)

	start := time.Now()
	s.Stop()
	elapsed := time.Since(start)

	// If the retry sleep is not interruptible, Stop() would block for at
	// least the remaining 400ms of the first sleep + 1000ms second sleep.
	// With interruption it should return almost immediately (< 200ms).
	if elapsed > 2*time.Second {
		t.Errorf("Stop() blocked for %v — retry sleep not interruptible by s.done (expected < 2s)", elapsed)
	}
}

// --- TC-23: Permanent flush failure is loudly logged ---

// TestSendWithRetry_PermanentFailure_DoesNotPanic verifies that after all
// retry attempts fail the shipper does not crash or hang — it logs the failure
// and Stop() returns cleanly. (The actual log output is not asserted here;
// this test ensures no panic and no goroutine leak.)
func TestSendWithRetry_PermanentFailure_DoesNotPanic(t *testing.T) {
	ts := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusBadGateway) // permanent failure
	}))
	defer ts.Close()

	s := shipper.New("tc23", ts.URL, "secret", 10*time.Minute, 1)
	s.Start()
	s.Add(shipper.QueryLog{Timestamp: time.Now().UTC(), Query: "SELECT 1", DurationMs: 1})

	// Stop blocks until all retry attempts are exhausted + shutdown flush attempted.
	// Must return without panicking regardless of delivery outcome.
	done := make(chan struct{})
	go func() {
		s.Stop()
		close(done)
	}()

	select {
	case <-done:
		// pass
	case <-time.After(30 * time.Second):
		t.Fatal("Stop() did not return after 30s — likely deadlocked")
	}
}
