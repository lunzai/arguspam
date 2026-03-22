//go:build integration

// Package integration contains end-to-end tests that require a real MySQL
// instance. They are excluded from the default `go test ./...` run and must
// be opted into explicitly:
//
//	go test -tags integration ./integration/ \
//	    -mysql-host=127.0.0.1 -mysql-port=3306 \
//	    -mysql-user=pam_test  -mysql-pass=testpass123 \
//	    -mysql-db=testdb
//
// The tests spin up an in-process iris Manager and a stub Laravel API server
// (httptest), so no external iris binary is needed.
//
// Covered test cases:
//   TC-01 — Basic start / connect / query / stop
//   TC-02 — Query log fields are complete and correct
//   TC-03 — Failed query is logged with its error
//   TC-10 — Client disconnect and reconnect within the same session
//   TC-11 — Status reflects client connected/disconnected state
//   TC-12 — No query loss on stop (shutdown flush)
//   TC-13 — StopAll (graceful shutdown) flushes all sessions
//   TC-16 — Prepared statement arguments are logged
//   TC-20 — Hard client disconnect does not break the session
//   TC-26 — USE DATABASE is forwarded to the upstream

package integration

import (
	"context"
	"encoding/json"
	"flag"
	"fmt"
	"io"
	"net"
	"net/http"
	"net/http/httptest"
	"strings"
	"sync"
	"testing"
	"time"

	"github.com/arguspam/iris/pool"
	"github.com/arguspam/iris/proxy"
	"github.com/arguspam/iris/shipper"
	gomysqlclient "github.com/go-mysql-org/go-mysql/client"
)

// ── flags ──────────────────────────────────────────────────────────────────

var (
	mysqlHost = flag.String("mysql-host", "127.0.0.1", "MySQL host")
	mysqlPort = flag.Int("mysql-port", 3306, "MySQL port")
	mysqlUser = flag.String("mysql-user", "pam_test", "MySQL JIT username")
	mysqlPass = flag.String("mysql-pass", "testpass123", "MySQL JIT password")
	mysqlDB   = flag.String("mysql-db", "testdb", "MySQL database to use")
)

// ── stub Laravel server ────────────────────────────────────────────────────

// stubLaravel captures query log batches POSTed by the shipper.
type stubLaravel struct {
	mu      sync.Mutex
	batches [][]shipper.QueryLog
}

func (s *stubLaravel) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	if r.Header.Get("X-Proxy-Secret") != "test-secret" {
		w.WriteHeader(http.StatusForbidden)
		return
	}
	body, _ := io.ReadAll(r.Body)
	var p struct {
		Queries []shipper.QueryLog `json:"queries"`
	}
	_ = json.Unmarshal(body, &p)
	s.mu.Lock()
	s.batches = append(s.batches, p.Queries)
	s.mu.Unlock()
	w.WriteHeader(http.StatusOK)
}

func (s *stubLaravel) allQueries() []shipper.QueryLog {
	s.mu.Lock()
	defer s.mu.Unlock()
	var all []shipper.QueryLog
	for _, b := range s.batches {
		all = append(all, b...)
	}
	return all
}

// ── test harness ───────────────────────────────────────────────────────────

type harness struct {
	stub    *stubLaravel
	ts      *httptest.Server
	manager *proxy.Manager
	pool    *pool.Pool
}

// newHarness wires an in-process Manager to a stub Laravel server.
// portBase..portBase+9 is the proxy port range (10 ports).
func newHarness(t *testing.T, portBase int) *harness {
	t.Helper()
	stub := &stubLaravel{}
	ts := httptest.NewServer(stub)
	t.Cleanup(ts.Close)

	p := pool.New(portBase, portBase+9)
	mgr := proxy.NewManager(p, ts.URL, "test-secret", "127.0.0.1", 500*time.Millisecond, 100)
	t.Cleanup(func() { mgr.StopAll(context.Background()) })

	return &harness{stub: stub, ts: ts, manager: mgr, pool: p}
}

func (h *harness) startSession(t *testing.T, sessionID string) int {
	t.Helper()
	port, err := h.manager.Start(proxy.StartRequest{
		SessionID:   sessionID,
		DBMS:        "mysql",
		JitUsername: *mysqlUser,
		JitPassword: *mysqlPass,
		DBHost:      *mysqlHost,
		DBPort:      *mysqlPort,
	})
	if err != nil {
		t.Fatalf("Start(%q): %v", sessionID, err)
	}
	return port
}

func (h *harness) stopSession(t *testing.T, sessionID string) proxy.StopResponse {
	t.Helper()
	resp, err := h.manager.Stop(sessionID)
	if err != nil {
		t.Fatalf("Stop(%q): %v", sessionID, err)
	}
	return resp
}

// dial opens a go-mysql client connection through the proxy port.
func dial(t *testing.T, proxyPort int, db string) *gomysqlclient.Conn {
	t.Helper()
	addr := fmt.Sprintf("127.0.0.1:%d", proxyPort)
	conn, err := gomysqlclient.Connect(addr, *mysqlUser, *mysqlPass, db)
	if err != nil {
		t.Fatalf("mysql connect to proxy :%d: %v", proxyPort, err)
	}
	t.Cleanup(func() { conn.Close() })
	return conn
}

// mustQuery runs a query on conn and fatals on error.
func mustQuery(t *testing.T, conn *gomysqlclient.Conn, query string) {
	t.Helper()
	if _, err := conn.Execute(query); err != nil {
		t.Fatalf("query %q: %v", query, err)
	}
}

// queryTexts extracts the Query field from a slice of QueryLog for error messages.
func queryTexts(qs []shipper.QueryLog) []string {
	out := make([]string, len(qs))
	for i, q := range qs {
		out[i] = q.Query
	}
	return out
}

// ── TC-01: Basic start / connect / query / stop ───────────────────────────

func TestBasicSessionFlow(t *testing.T) {
	h := newHarness(t, 46000)
	port := h.startSession(t, "tc01")

	conn := dial(t, port, *mysqlDB)
	mustQuery(t, conn, "SELECT 1")
	mustQuery(t, conn, "SELECT 2")
	conn.Close()

	resp := h.stopSession(t, "tc01")
	if !resp.Flushed {
		t.Error("expected flushed=true")
	}
	if resp.TotalQueries != 2 {
		t.Errorf("expected total_queries=2, got %d", resp.TotalQueries)
	}

	got := h.stub.allQueries()
	if len(got) != 2 {
		t.Fatalf("expected 2 queries in stub, got %d", len(got))
	}
	if got[0].Query != "SELECT 1" || got[1].Query != "SELECT 2" {
		t.Errorf("wrong query order: %v", queryTexts(got))
	}
}

// ── TC-02: Query log fields are complete and correct ──────────────────────

func TestQueryLogFields(t *testing.T) {
	h := newHarness(t, 46020)
	port := h.startSession(t, "tc02")

	conn := dial(t, port, *mysqlDB)
	mustQuery(t, conn, "SELECT 1")
	conn.Close()

	h.stopSession(t, "tc02")
	got := h.stub.allQueries()
	if len(got) != 1 {
		t.Fatalf("expected 1 query, got %d", len(got))
	}
	q := got[0]

	if q.Query != "SELECT 1" {
		t.Errorf("wrong query: %q", q.Query)
	}
	if q.Timestamp.IsZero() {
		t.Error("timestamp is zero")
	}
	if q.Timestamp.Location() != time.UTC {
		t.Errorf("timestamp not UTC: %v", q.Timestamp.Location())
	}
	if q.DurationMs < 0 {
		t.Errorf("negative duration_ms: %d", q.DurationMs)
	}
	if q.Error != "" {
		t.Errorf("unexpected error field on successful query: %q", q.Error)
	}
}

// ── TC-03: Failed query is logged with its error ──────────────────────────

func TestFailedQueryIsLogged(t *testing.T) {
	h := newHarness(t, 46040)
	port := h.startSession(t, "tc03")

	conn := dial(t, port, *mysqlDB)
	// Intentionally run a query that will fail.
	if _, err := conn.Execute("SELECT * FROM iris_nonexistent_table_xyz_abc"); err == nil {
		t.Fatal("expected query to fail, got nil error")
	}
	conn.Close()

	h.stopSession(t, "tc03")
	got := h.stub.allQueries()
	if len(got) != 1 {
		t.Fatalf("expected 1 log entry even for failed query, got %d", len(got))
	}
	if got[0].Error == "" {
		t.Error("expected non-empty error field for failed query")
	}
}

// ── TC-10: Client disconnect and reconnect within the same session ─────────

func TestClientReconnectWithinSession(t *testing.T) {
	h := newHarness(t, 46060)
	port := h.startSession(t, "tc10")

	// First connection.
	conn1 := dial(t, port, *mysqlDB)
	mustQuery(t, conn1, "SELECT 1")
	conn1.Close()

	time.Sleep(100 * time.Millisecond) // let the accept loop cycle

	// Second connection to the same proxy port — session window still open.
	conn2 := dial(t, port, *mysqlDB)
	mustQuery(t, conn2, "SELECT 2")
	conn2.Close()

	resp := h.stopSession(t, "tc10")
	if resp.TotalQueries != 2 {
		t.Errorf("expected 2 total queries across both connections, got %d", resp.TotalQueries)
	}
}

// ── TC-11: Status reflects client connected/disconnected state ────────────

func TestStatusReflectsConnectedState(t *testing.T) {
	h := newHarness(t, 46080)
	port := h.startSession(t, "tc11")

	status, err := h.manager.Status("tc11")
	if err != nil {
		t.Fatalf("Status: %v", err)
	}
	if !status.Active {
		t.Error("expected active=true after start")
	}
	if status.ClientConnected {
		t.Error("expected client_connected=false before any client connects")
	}
	if status.Port != port {
		t.Errorf("expected port=%d, got %d", port, status.Port)
	}
	if status.SessionStartedAt.IsZero() {
		t.Error("session_started_at is zero")
	}

	conn := dial(t, port, *mysqlDB)
	mustQuery(t, conn, "SELECT 1")

	time.Sleep(50 * time.Millisecond)
	status, err = h.manager.Status("tc11")
	if err != nil {
		t.Fatalf("Status after connect: %v", err)
	}
	if !status.ClientConnected {
		t.Error("expected client_connected=true while client is connected")
	}

	conn.Close()
	time.Sleep(100 * time.Millisecond)
	status, err = h.manager.Status("tc11")
	if err != nil {
		t.Fatalf("Status after disconnect: %v", err)
	}
	if status.ClientConnected {
		t.Error("expected client_connected=false after client disconnects")
	}

	h.stopSession(t, "tc11")
}

// ── TC-12: No query loss on stop (shutdown flush) ─────────────────────────

func TestNoQueryLossOnStop(t *testing.T) {
	// Very large batch size + long interval so nothing auto-flushes.
	// All queries must reach the stub via the stop-triggered final flush.
	p := pool.New(46100, 46109)
	stub := &stubLaravel{}
	ts := httptest.NewServer(stub)
	t.Cleanup(ts.Close)
	mgr := proxy.NewManager(p, ts.URL, "test-secret", "127.0.0.1", 10*time.Minute, 500)
	t.Cleanup(func() { mgr.StopAll(context.Background()) })

	port, err := mgr.Start(proxy.StartRequest{
		SessionID: "tc12", DBMS: "mysql",
		JitUsername: *mysqlUser, JitPassword: *mysqlPass,
		DBHost: *mysqlHost, DBPort: *mysqlPort,
	})
	if err != nil {
		t.Fatalf("start: %v", err)
	}

	conn, err := gomysqlclient.Connect(fmt.Sprintf("127.0.0.1:%d", port), *mysqlUser, *mysqlPass, *mysqlDB)
	if err != nil {
		t.Fatalf("connect: %v", err)
	}

	const n = 7
	for i := range n {
		if _, err := conn.Execute(fmt.Sprintf("SELECT %d", i+1)); err != nil {
			t.Fatalf("query %d: %v", i+1, err)
		}
	}
	conn.Close()

	resp, err := mgr.Stop("tc12")
	if err != nil {
		t.Fatalf("stop: %v", err)
	}
	if resp.TotalQueries != n {
		t.Errorf("expected total_queries=%d, got %d", n, resp.TotalQueries)
	}
	if len(stub.allQueries()) != n {
		t.Errorf("expected %d queries in stub, got %d: %v", n, len(stub.allQueries()), queryTexts(stub.allQueries()))
	}
}

// ── TC-13: StopAll flushes all active sessions ────────────────────────────

func TestStopAll_FlushesAllSessions(t *testing.T) {
	const numSessions = 3
	p := pool.New(46120, 46129)
	stub := &stubLaravel{}
	ts := httptest.NewServer(stub)
	t.Cleanup(ts.Close)
	mgr := proxy.NewManager(p, ts.URL, "test-secret", "127.0.0.1", 10*time.Minute, 500)

	for i := range numSessions {
		port, err := mgr.Start(proxy.StartRequest{
			SessionID:   fmt.Sprintf("tc13-%d", i),
			DBMS:        "mysql",
			JitUsername: *mysqlUser, JitPassword: *mysqlPass,
			DBHost: *mysqlHost, DBPort: *mysqlPort,
		})
		if err != nil {
			t.Fatalf("start session %d: %v", i, err)
		}
		conn, err := gomysqlclient.Connect(fmt.Sprintf("127.0.0.1:%d", port), *mysqlUser, *mysqlPass, *mysqlDB)
		if err != nil {
			t.Fatalf("connect session %d: %v", i, err)
		}
		if _, err := conn.Execute("SELECT 1"); err != nil {
			t.Fatalf("query session %d: %v", i, err)
		}
		conn.Close()
	}

	ctx, cancel := context.WithTimeout(context.Background(), 15*time.Second)
	defer cancel()
	mgr.StopAll(ctx)

	got := stub.allQueries()
	if len(got) != numSessions {
		t.Errorf("expected %d total queries, got %d", numSessions, len(got))
	}
}

// ── TC-16: Prepared statement arguments are logged ────────────────────────

func TestPreparedStatementArgsLogged(t *testing.T) {
	h := newHarness(t, 46140)
	port := h.startSession(t, "tc16")

	conn := dial(t, port, *mysqlDB)
	stmt, err := conn.Prepare("SELECT ?")
	if err != nil {
		t.Fatalf("prepare: %v", err)
	}
	if _, err := stmt.Execute(42); err != nil {
		t.Fatalf("execute prepared: %v", err)
	}
	stmt.Close()
	conn.Close()

	h.stopSession(t, "tc16")
	got := h.stub.allQueries()
	if len(got) == 0 {
		t.Fatal("no queries received by stub")
	}
	found := false
	for _, q := range got {
		if strings.Contains(q.Query, "-- args:") && strings.Contains(q.Query, "42") {
			found = true
			break
		}
	}
	if !found {
		t.Errorf("prepared stmt args not present in log; queries: %v", queryTexts(got))
	}
}

// ── TC-20: Hard client disconnect does not break the session ──────────────

func TestHardDisconnect_SessionRemainsActive(t *testing.T) {
	h := newHarness(t, 46160)
	port := h.startSession(t, "tc20")

	// Open a raw TCP connection to the proxy port and close it abruptly
	// (SO_LINGER=0 → RST). This simulates a client crash.
	rawConn, err := net.DialTimeout("tcp", fmt.Sprintf("127.0.0.1:%d", port), 2*time.Second)
	if err != nil {
		t.Fatalf("raw dial: %v", err)
	}
	rawConn.(*net.TCPConn).SetLinger(0)
	rawConn.Close()

	time.Sleep(200 * time.Millisecond)

	// The session must still be active.
	status, err := h.manager.Status("tc20")
	if err != nil {
		t.Fatalf("Status: %v", err)
	}
	if !status.Active {
		t.Error("session became inactive after hard client disconnect")
	}

	// A proper MySQL client must still connect successfully.
	conn := dial(t, port, *mysqlDB)
	mustQuery(t, conn, "SELECT 1")
	conn.Close()

	h.stopSession(t, "tc20")
}

// ── TC-26: USE DATABASE is forwarded to the upstream ─────────────────────

func TestUseDatabase_IsForwarded(t *testing.T) {
	h := newHarness(t, 46180)
	port := h.startSession(t, "tc26")

	// Connect without specifying a database.
	addr := fmt.Sprintf("127.0.0.1:%d", port)
	conn, err := gomysqlclient.Connect(addr, *mysqlUser, *mysqlPass, "")
	if err != nil {
		t.Fatalf("connect (no db): %v", err)
	}
	t.Cleanup(func() { conn.Close() })

	if err := conn.UseDB(*mysqlDB); err != nil {
		t.Fatalf("UseDB: %v", err)
	}

	result, err := conn.Execute("SELECT DATABASE()")
	if err != nil {
		t.Fatalf("SELECT DATABASE(): %v", err)
	}
	val, _ := result.GetString(0, 0)
	if val != *mysqlDB {
		t.Errorf("SELECT DATABASE() = %q, want %q", val, *mysqlDB)
	}

	conn.Close()
	h.stopSession(t, "tc26")
}
