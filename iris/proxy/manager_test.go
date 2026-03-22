package proxy_test

// Automated coverage for:
//   TC-07 — Pool exhaustion returns error; pool recovers after stop
//   TC-08 — Duplicate session ID is rejected
//   TC-09 — Stop non-existent session returns ErrSessionNotFound
//   TC-18 — Concurrent Stop during Start never leaks a port
//   TC-19 — Stop cancels an in-progress upstream dial (< dialTimeout)
//   TC-21 — Double-stop is idempotent (second call returns ErrSessionNotFound)
//   TC-27 — Concurrent starts allocate unique ports
//   TC-28 — StopAll releases every port back to the pool
//
// No real MySQL is required: sessions open TCP listeners but the MySQL dial
// only happens inside handleConn, which only runs when a client connects.
// Tests that need the dial to block use startBlockingMySQLServer below.
//
// Port ranges are non-overlapping so tests can run in parallel safely.

import (
	"context"
	"fmt"
	"net"
	"sync"
	"testing"
	"time"

	"github.com/arguspam/iris/pool"
	"github.com/arguspam/iris/proxy"
)

// newManager builds a Manager wired to a dummy argus URL (port 1 is not
// listening, but the shipper never fires because no queries are added).
func newManager(t *testing.T, portStart, portEnd int) (*proxy.Manager, *pool.Pool) {
	t.Helper()
	p := pool.New(portStart, portEnd)
	mgr := proxy.NewManager(p, "http://127.0.0.1:1", "secret", "127.0.0.1", 10*time.Minute, 100)
	return mgr, p
}

func req(sessionID, dbHost string, dbPort int) proxy.StartRequest {
	return proxy.StartRequest{
		SessionID:   sessionID,
		DBMS:        "mysql",
		JitUsername: "u",
		JitPassword: "p",
		DBHost:      dbHost,
		DBPort:      dbPort,
	}
}

// startBlockingMySQLServer returns a TCP listener that accepts connections but
// never sends the MySQL server greeting. go-mysql's Dial blocks on the MySQL
// handshake until its context is cancelled, making this useful for simulating
// an upstream that is reachable at the TCP level but unresponsive at the
// MySQL protocol level.
func startBlockingMySQLServer(t *testing.T) (host string, port int) {
	t.Helper()
	ln, err := net.Listen("tcp", "127.0.0.1:0")
	if err != nil {
		t.Fatalf("blockingMySQL listen: %v", err)
	}
	t.Cleanup(func() { ln.Close() })

	go func() {
		for {
			conn, err := ln.Accept()
			if err != nil {
				return // listener closed — exit
			}
			// Hold the TCP connection open for 60s but never write the MySQL
			// server greeting, so go-mysql's handshake read blocks.
			go func(c net.Conn) {
				defer c.Close()
				time.Sleep(60 * time.Second)
			}(conn)
		}
	}()

	addr := ln.Addr().(*net.TCPAddr)
	return "127.0.0.1", addr.Port
}

// --- TC-09 ---

func TestManager_StopNonExistent_ReturnsNotFound(t *testing.T) {
	mgr, _ := newManager(t, 47000, 47005)
	_, err := mgr.Stop("ghost-session")
	if err != proxy.ErrSessionNotFound {
		t.Fatalf("expected ErrSessionNotFound, got %v", err)
	}
}

// --- TC-08 ---

func TestManager_DuplicateSessionID_Rejected(t *testing.T) {
	mgr, p := newManager(t, 47010, 47015)
	defer mgr.StopAll(context.Background())

	if _, err := mgr.Start(req("dup", "127.0.0.1", 1)); err != nil {
		t.Fatalf("first start: %v", err)
	}
	if p.ActiveCount() != 1 {
		t.Fatalf("expected 1 allocated port, got %d", p.ActiveCount())
	}

	_, err := mgr.Start(req("dup", "127.0.0.1", 1))
	if err == nil {
		t.Fatal("expected error for duplicate session ID, got nil")
	}
	// No extra port must have been consumed.
	if p.ActiveCount() != 1 {
		t.Fatalf("expected 1 allocated port after duplicate start, got %d", p.ActiveCount())
	}
}

// --- TC-07 ---

func TestManager_PoolExhaustion_ReturnsError(t *testing.T) {
	mgr, _ := newManager(t, 47020, 47021) // 2 ports
	defer mgr.StopAll(context.Background())

	if _, err := mgr.Start(req("e1", "127.0.0.1", 1)); err != nil {
		t.Fatalf("start e1: %v", err)
	}
	if _, err := mgr.Start(req("e2", "127.0.0.1", 1)); err != nil {
		t.Fatalf("start e2: %v", err)
	}
	_, err := mgr.Start(req("e3", "127.0.0.1", 1))
	if err != pool.ErrPoolExhausted {
		t.Fatalf("expected ErrPoolExhausted, got %v", err)
	}
}

func TestManager_PoolRecovery_AfterStop(t *testing.T) {
	mgr, _ := newManager(t, 47030, 47030) // 1 port

	if _, err := mgr.Start(req("r1", "127.0.0.1", 1)); err != nil {
		t.Fatalf("start: %v", err)
	}
	if _, err := mgr.Start(req("r2", "127.0.0.1", 1)); err != pool.ErrPoolExhausted {
		t.Fatalf("expected exhaustion before stop, got %v", err)
	}
	if _, err := mgr.Stop("r1"); err != nil {
		t.Fatalf("stop r1: %v", err)
	}
	// Port is back — r2 must succeed now.
	if _, err := mgr.Start(req("r2", "127.0.0.1", 1)); err != nil {
		t.Fatalf("start after pool recovery: %v", err)
	}
	mgr.Stop("r2")
}

// --- TC-21 ---

func TestManager_DoubleStop_Idempotent(t *testing.T) {
	mgr, p := newManager(t, 47040, 47040)

	if _, err := mgr.Start(req("ds", "127.0.0.1", 1)); err != nil {
		t.Fatalf("start: %v", err)
	}

	resp, err := mgr.Stop("ds")
	if err != nil {
		t.Fatalf("first stop: %v", err)
	}
	if !resp.Flushed {
		t.Error("expected flushed=true on first stop")
	}
	if p.ActiveCount() != 0 {
		t.Fatalf("expected 0 active ports after stop, got %d", p.ActiveCount())
	}

	// Second stop must not panic and must return ErrSessionNotFound.
	_, err = mgr.Stop("ds")
	if err != proxy.ErrSessionNotFound {
		t.Fatalf("second stop: expected ErrSessionNotFound, got %v", err)
	}
	// Port count must remain 0 — no double-release.
	if p.ActiveCount() != 0 {
		t.Fatalf("expected 0 active ports after double-stop, got %d", p.ActiveCount())
	}
}

// --- TC-18 ---

func TestManager_StopDuringStart_NoPortLeak(t *testing.T) {
	// Single port. Race Start against Stop. Regardless of which wins, the
	// port must always be returned so a subsequent session can be started.
	mgr, p := newManager(t, 47050, 47050)

	var wg sync.WaitGroup
	wg.Add(2)
	go func() {
		defer wg.Done()
		mgr.Start(req("race", "127.0.0.1", 1)) // may succeed or fail
	}()
	go func() {
		defer wg.Done()
		time.Sleep(1 * time.Millisecond)
		mgr.Stop("race") // may return NotFound or succeed
	}()
	wg.Wait()

	// The key invariant: port must be back in the pool.
	if p.ActiveCount() != 0 {
		t.Fatalf("port leaked after Start+Stop race: %d ports still allocated", p.ActiveCount())
	}
	// Confirm by starting a new session.
	if _, err := mgr.Start(req("post-race", "127.0.0.1", 1)); err != nil {
		t.Fatalf("post-race start failed (port was not released): %v", err)
	}
	mgr.Stop("post-race")
}

// --- TC-19 ---

func TestManager_StopCancelsDial(t *testing.T) {
	// The fake MySQL server accepts TCP connections but never sends the MySQL
	// server greeting. When a client connects to the proxy port, handleConn
	// calls proxymysql.Dial, which blocks on the MySQL handshake.
	// Stop() must cancel the dial via s.stopped → dialCtx, not wait for
	// dialTimeout (15s).
	fakeHost, fakePort := startBlockingMySQLServer(t)

	mgr, _ := newManager(t, 47060, 47060)
	proxyPort, err := mgr.Start(req("dial-test", fakeHost, fakePort))
	if err != nil {
		t.Fatalf("start: %v", err)
	}

	// Connect a raw TCP client to the proxy port to trigger handleConn.
	// handleConn then calls proxymysql.Dial which blocks on the fake server.
	go func() {
		conn, err := net.DialTimeout("tcp", fmt.Sprintf("127.0.0.1:%d", proxyPort), 2*time.Second)
		if err == nil {
			defer conn.Close()
			time.Sleep(10 * time.Second) // keep connection open so handleConn stays alive
		}
	}()

	// Give handleConn time to start and block inside Dial.
	time.Sleep(200 * time.Millisecond)

	// Stop must return well under dialTimeout (15s). Target: < 3s.
	start := time.Now()
	if _, err := mgr.Stop("dial-test"); err != nil {
		t.Fatalf("stop: %v", err)
	}
	elapsed := time.Since(start)
	if elapsed > 3*time.Second {
		t.Errorf("Stop took %v — dial cancellation not working (dialTimeout is 15s)", elapsed)
	}
}

// --- TC-27 ---

func TestManager_ConcurrentStarts_UniquePortsAllocated(t *testing.T) {
	const n = 15
	mgr, _ := newManager(t, 47070, 47084) // exactly n ports
	defer mgr.StopAll(context.Background())

	var (
		mu    sync.Mutex
		ports []int
		wg    sync.WaitGroup
	)
	wg.Add(n)
	for i := range n {
		go func(i int) {
			defer wg.Done()
			port, err := mgr.Start(req(fmt.Sprintf("cs-%d", i), "127.0.0.1", 1))
			if err != nil {
				t.Errorf("goroutine %d: start failed: %v", i, err)
				return
			}
			mu.Lock()
			ports = append(ports, port)
			mu.Unlock()
		}(i)
	}
	wg.Wait()

	if len(ports) != n {
		t.Fatalf("expected %d successful starts, got %d", n, len(ports))
	}
	seen := make(map[int]bool, n)
	for _, p := range ports {
		if seen[p] {
			t.Errorf("port %d was allocated to more than one session", p)
		}
		seen[p] = true
	}

	// Pool is now full — one more start must be rejected.
	_, err := mgr.Start(req("overflow", "127.0.0.1", 1))
	if err != pool.ErrPoolExhausted {
		t.Errorf("expected ErrPoolExhausted after pool full, got %v", err)
	}
}

// --- TC-28 ---

func TestManager_StopAll_ReleasesAllPorts(t *testing.T) {
	const n = 5
	mgr, p := newManager(t, 47090, 47094) // exactly n ports

	for i := range n {
		if _, err := mgr.Start(req(fmt.Sprintf("sa-%d", i), "127.0.0.1", 1)); err != nil {
			t.Fatalf("start %d: %v", i, err)
		}
	}
	if p.ActiveCount() != n {
		t.Fatalf("expected %d active ports before StopAll, got %d", n, p.ActiveCount())
	}

	mgr.StopAll(context.Background())

	if p.ActiveCount() != 0 {
		t.Fatalf("expected 0 active ports after StopAll, got %d", p.ActiveCount())
	}
}
