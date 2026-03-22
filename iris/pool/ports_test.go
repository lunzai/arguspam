package pool_test

import (
	"fmt"
	"sync"
	"testing"

	"github.com/arguspam/iris/pool"
)

func TestAcquireRelease(t *testing.T) {
	p := pool.New(15000, 15002) // 3 ports: 15000, 15001, 15002

	port, err := p.Acquire("sess-1")
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if port < 15000 || port > 15002 {
		t.Fatalf("port %d out of range", port)
	}
	if p.ActiveCount() != 1 {
		t.Fatalf("expected 1 active, got %d", p.ActiveCount())
	}

	p.Release(port)
	if p.ActiveCount() != 0 {
		t.Fatalf("expected 0 active after release, got %d", p.ActiveCount())
	}
}

func TestAcquireExhaustion(t *testing.T) {
	p := pool.New(15000, 15001) // 2 ports

	_, err := p.Acquire("sess-1")
	if err != nil {
		t.Fatalf("first acquire failed: %v", err)
	}
	_, err = p.Acquire("sess-2")
	if err != nil {
		t.Fatalf("second acquire failed: %v", err)
	}

	_, err = p.Acquire("sess-3")
	if err == nil {
		t.Fatal("expected error on exhausted pool, got nil")
	}
	if err != pool.ErrPoolExhausted {
		t.Fatalf("expected ErrPoolExhausted, got %v", err)
	}
}

func TestReleaseAndReacquire(t *testing.T) {
	p := pool.New(15000, 15000) // single port

	port, err := p.Acquire("sess-1")
	if err != nil {
		t.Fatalf("acquire failed: %v", err)
	}

	p.Release(port)

	port2, err := p.Acquire("sess-2")
	if err != nil {
		t.Fatalf("reacquire failed: %v", err)
	}
	if port2 != port {
		t.Fatalf("expected same port %d, got %d", port, port2)
	}
}

func TestReleaseFor_CorrectOwner(t *testing.T) {
	p := pool.New(15000, 15001)

	port, err := p.Acquire("sess-1")
	if err != nil {
		t.Fatalf("acquire failed: %v", err)
	}

	p.ReleaseFor(port, "sess-1")
	if p.ActiveCount() != 0 {
		t.Fatal("expected port to be released by correct owner")
	}
}

func TestReleaseFor_WrongOwner_IsNoOp(t *testing.T) {
	p := pool.New(15000, 15001)

	port, err := p.Acquire("sess-1")
	if err != nil {
		t.Fatalf("acquire failed: %v", err)
	}

	p.ReleaseFor(port, "sess-2") // wrong owner — must not release
	if p.ActiveCount() != 1 {
		t.Fatal("expected port to remain allocated when wrong owner calls ReleaseFor")
	}

	p.ReleaseFor(port, "sess-1") // correct owner — now releases
	if p.ActiveCount() != 0 {
		t.Fatal("expected port to be released by correct owner")
	}
}

func TestReleaseFor_UnallocatedPort_IsNoOp(t *testing.T) {
	p := pool.New(15000, 15001)
	p.ReleaseFor(15000, "sess-1") // not allocated — must not panic
	if p.ActiveCount() != 0 {
		t.Fatal("expected no change for unallocated port")
	}
}

func TestConcurrentAcquireRelease(t *testing.T) {
	const n = 100
	p := pool.New(20000, 20099) // 100 ports

	var wg sync.WaitGroup
	ports := make(chan int, n)

	for i := range n {
		wg.Add(1)
		go func(i int) {
			defer wg.Done()
			port, err := p.Acquire(fmt.Sprintf("sess-%d", i))
			if err != nil {
				t.Errorf("goroutine %d: acquire failed: %v", i, err)
				return
			}
			ports <- port
		}(i)
	}
	wg.Wait()
	close(ports)

	if p.ActiveCount() != n {
		t.Fatalf("expected %d active, got %d", n, p.ActiveCount())
	}

	for port := range ports {
		p.Release(port)
	}
	if p.ActiveCount() != 0 {
		t.Fatalf("expected 0 active after all releases, got %d", p.ActiveCount())
	}
}
