package pool

import (
	"errors"
	"fmt"
	"sync"
)

var ErrPoolExhausted = errors.New("port pool exhausted")

// Pool manages a range of ports available for proxy sessions.
type Pool struct {
	mu        sync.Mutex
	available []int
	allocated map[int]string // port -> sessionID
}

func New(start, end int) *Pool {
	if start > end {
		panic(fmt.Sprintf("pool: invalid port range %d-%d (start > end)", start, end))
	}
	avail := make([]int, 0, end-start+1)
	for p := start; p <= end; p++ {
		avail = append(avail, p)
	}
	return &Pool{
		available: avail,
		allocated: make(map[int]string),
	}
}

// Acquire returns a free port and records it against sessionID.
func (p *Pool) Acquire(sessionID string) (int, error) {
	p.mu.Lock()
	defer p.mu.Unlock()

	if len(p.available) == 0 {
		return 0, ErrPoolExhausted
	}

	port := p.available[len(p.available)-1]
	p.available = p.available[:len(p.available)-1]
	p.allocated[port] = sessionID
	return port, nil
}

// Release returns a port to the pool.
// Calling Release on a port that was not allocated is a no-op.
func (p *Pool) Release(port int) {
	p.mu.Lock()
	defer p.mu.Unlock()

	if _, ok := p.allocated[port]; !ok {
		return
	}
	delete(p.allocated, port)
	p.available = append(p.available, port)
}

// ReleaseFor returns a port to the pool only if it is currently allocated to
// the given sessionID. If the port belongs to a different session or is not
// allocated at all, the call is a no-op. This prevents a session from
// accidentally releasing a port it does not own.
func (p *Pool) ReleaseFor(port int, sessionID string) {
	p.mu.Lock()
	defer p.mu.Unlock()

	if owner, ok := p.allocated[port]; !ok || owner != sessionID {
		return
	}
	delete(p.allocated, port)
	p.available = append(p.available, port)
}

// ActiveCount returns the number of currently allocated ports.
func (p *Pool) ActiveCount() int {
	p.mu.Lock()
	defer p.mu.Unlock()
	return len(p.allocated)
}
