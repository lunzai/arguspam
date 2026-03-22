package mysql

import (
	"context"
	"fmt"
	"net"
	"time"

	"github.com/go-mysql-org/go-mysql/client"
	gomysql "github.com/go-mysql-org/go-mysql/mysql"
)

// clientUpstream adapts a go-mysql client.Conn to the Upstream interface.
type clientUpstream struct {
	conn *client.Conn
}

// dialTimeout is the maximum time allowed to establish the upstream TCP
// connection and complete the MySQL handshake. Prevents blocking the accept
// loop indefinitely when the upstream host drops packets.
const dialTimeout = 15 * time.Second

// Dial opens a connection to the target database using JIT credentials.
// ctx is used as the parent for the dial deadline so that a session stop
// (which cancels the caller's context) immediately aborts an in-progress dial
// rather than letting it block for the full dialTimeout.
// The host and port are intentionally omitted from the returned error to
// prevent upstream addresses from leaking into logs.
func Dial(ctx context.Context, host string, port int, user, password string) (Upstream, error) {
	addr := fmt.Sprintf("%s:%d", host, port)
	ctx, cancel := context.WithTimeout(ctx, dialTimeout)
	defer cancel()

	// go-mysql's ConnectWithDialer passes ctx only to the TCP dial phase
	// (net.Dialer.DialContext). The MySQL handshake reads that follow are
	// uncancellable by default, so a context cancellation (e.g. session stop)
	// would not unblock them until the dialTimeout fires.
	//
	// The dialer wrapper below fixes this: it sets an absolute deadline from
	// the context on the established TCP connection (bounding handshake reads),
	// then starts a goroutine that immediately expires the deadline if the
	// context is cancelled before ConnectWithDialer returns. A second done
	// channel (watchDone) stops that goroutine once the handshake is complete,
	// preventing it from expiring the deadline on an already-live connection.
	watchDone := make(chan struct{})
	dialer := func(dialCtx context.Context, network, address string) (net.Conn, error) {
		c, err := (&net.Dialer{}).DialContext(dialCtx, network, address)
		if err != nil {
			return nil, err
		}
		// Absolute deadline from the context bounds all handshake reads.
		if dl, ok := dialCtx.Deadline(); ok {
			c.SetDeadline(dl)
		}
		// On context cancellation, expire the deadline immediately so the
		// handshake read unblocks without waiting for the absolute deadline.
		go func() {
			select {
			case <-dialCtx.Done():
				c.SetDeadline(time.Now())
			case <-watchDone:
				// ConnectWithDialer returned — stop watching.
			}
		}()
		return c, nil
	}

	conn, err := client.ConnectWithDialer(ctx, "tcp", addr, user, password, "", dialer)
	close(watchDone) // signal the watcher goroutine regardless of outcome
	if err != nil {
		// Do not wrap err: the underlying message contains the host:port which
		// must not appear in logs or error responses per the security design.
		return nil, fmt.Errorf("upstream connection failed")
	}
	return &clientUpstream{conn: conn}, nil
}

func (u *clientUpstream) Execute(command string, args ...any) (*gomysql.Result, error) {
	return u.conn.Execute(command, args...)
}

func (u *clientUpstream) UseDB(dbName string) error {
	return u.conn.UseDB(dbName)
}

func (u *clientUpstream) FieldList(table string, wildcard string) ([]*gomysql.Field, error) {
	return u.conn.FieldList(table, wildcard)
}

func (u *clientUpstream) Prepare(query string) (Stmt, error) {
	stmt, err := u.conn.Prepare(query)
	if err != nil {
		return nil, err
	}
	return &clientStmt{stmt: stmt}, nil
}

func (u *clientUpstream) Close() error {
	return u.conn.Close()
}

// clientStmt adapts a client.Stmt to the Stmt interface.
type clientStmt struct {
	stmt *client.Stmt
}

func (s *clientStmt) Execute(args ...any) (*gomysql.Result, error) {
	return s.stmt.Execute(args...)
}

func (s *clientStmt) Close() error {
	return s.stmt.Close()
}

func (s *clientStmt) ParamNum() int {
	return s.stmt.ParamNum()
}

func (s *clientStmt) ColumnNum() int {
	return s.stmt.ColumnNum()
}
