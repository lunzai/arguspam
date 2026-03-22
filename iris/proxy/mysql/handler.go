// Package mysql implements the MySQL wire protocol proxy handler.
// It acts as a MySQL server to the JIT user's SQL client, and as a MySQL
// client to the real target database, transparently forwarding all queries
// and logging them to a Shipper for audit.
package mysql

import (
	"fmt"
	"time"

	"github.com/arguspam/iris/shipper"
	gomysql "github.com/go-mysql-org/go-mysql/mysql"
)

// LogAdder is the minimal interface the Handler needs from a shipper.
type LogAdder interface {
	Add(q shipper.QueryLog)
}

// Handler implements server.Handler (go-mysql).
// One Handler is created per client connection and holds the upstream connection.
type Handler struct {
	upstream Upstream
	ship     LogAdder
}

// Upstream wraps a go-mysql client connection for upstream query execution.
type Upstream interface {
	Execute(command string, args ...any) (*gomysql.Result, error)
	UseDB(dbName string) error
	FieldList(table string, wildcard string) ([]*gomysql.Field, error)
	Prepare(query string) (Stmt, error)
	Close() error
}

// Stmt wraps a prepared statement on the upstream connection.
type Stmt interface {
	Execute(args ...any) (*gomysql.Result, error)
	Close() error
	ParamNum() int
	ColumnNum() int
}

// New creates a Handler with the given upstream connection and log adder.
func New(upstream Upstream, ship LogAdder) *Handler {
	return &Handler{upstream: upstream, ship: ship}
}

func (h *Handler) UseDB(dbName string) error {
	return h.upstream.UseDB(dbName)
}

func (h *Handler) HandleQuery(query string) (*gomysql.Result, error) {
	start := time.Now()
	result, err := h.upstream.Execute(query)
	h.log(query, time.Since(start), result, err)
	return result, err
}

func (h *Handler) HandleFieldList(table string, fieldWildcard string) ([]*gomysql.Field, error) {
	return h.upstream.FieldList(table, fieldWildcard)
}

func (h *Handler) HandleStmtPrepare(query string) (params int, columns int, ctx any, err error) {
	stmt, err := h.upstream.Prepare(query)
	if err != nil {
		return 0, 0, nil, err
	}
	return stmt.ParamNum(), stmt.ColumnNum(), stmt, nil
}

// maxArgValueLen caps the string representation of each prepared statement
// argument in audit logs. Prevents large BLOBs or binary values from producing
// unbounded log entries while still capturing the resolved values for audit.
// NOTE: all bound parameter values — including potentially sensitive data — are
// intentionally logged for PAM audit purposes. Ensure log storage is secured
// with appropriate access controls.
const maxArgValueLen = 256

func (h *Handler) HandleStmtExecute(ctx any, query string, args []any) (*gomysql.Result, error) {
	stmt, ok := ctx.(Stmt)
	if !ok {
		return nil, fmt.Errorf("invalid statement context")
	}
	start := time.Now()
	result, err := stmt.Execute(args...)
	// Append bound args to the template so the audit log captures the resolved
	// values, not just the prepared statement placeholder text.
	logged := query
	if len(args) > 0 {
		logged = fmt.Sprintf("%s -- args: %v", query, truncateArgValues(args))
	}
	h.log(logged, time.Since(start), result, err)
	return result, err
}

// truncateArgValues returns string representations of args, each capped at
// maxArgValueLen runes to prevent unbounded log entry sizes.
// Truncation is rune-aware to avoid splitting multi-byte UTF-8 characters.
func truncateArgValues(args []any) []string {
	out := make([]string, len(args))
	for i, a := range args {
		s := fmt.Sprintf("%v", a)
		if runes := []rune(s); len(runes) > maxArgValueLen {
			s = string(runes[:maxArgValueLen]) + "…"
		}
		out[i] = s
	}
	return out
}

func (h *Handler) HandleStmtClose(ctx any) error {
	stmt, ok := ctx.(Stmt)
	if !ok {
		return nil
	}
	return stmt.Close()
}

func (h *Handler) HandleOtherCommand(cmd byte, data []byte) error {
	return gomysql.NewError(gomysql.ER_UNKNOWN_ERROR, fmt.Sprintf("command %d not supported", cmd))
}

// log records a query event to the shipper.
func (h *Handler) log(query string, dur time.Duration, result *gomysql.Result, execErr error) {
	entry := shipper.QueryLog{
		Timestamp:  time.Now().UTC(),
		Query:      query,
		DurationMs: dur.Milliseconds(),
	}
	if result != nil {
		entry.RowsAffected = int64(result.AffectedRows)
	}
	if execErr != nil {
		entry.Error = execErr.Error()
	}
	h.ship.Add(entry)
}
