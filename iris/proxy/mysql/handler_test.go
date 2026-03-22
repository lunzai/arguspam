package mysql_test

import (
	"errors"
	"strings"
	"sync"
	"testing"
	"time"
	"unicode/utf8"

	proxymysql "github.com/arguspam/iris/proxy/mysql"
	"github.com/arguspam/iris/shipper"
	gomysql "github.com/go-mysql-org/go-mysql/mysql"
)

// --- mocks ---

type mockUpstream struct {
	executeFn   func(string, ...any) (*gomysql.Result, error)
	useDBFn     func(string) error
	fieldListFn func(string, string) ([]*gomysql.Field, error)
	prepareFn   func(string) (proxymysql.Stmt, error)
	closeFn     func() error
}

func (m *mockUpstream) Execute(cmd string, args ...any) (*gomysql.Result, error) {
	if m.executeFn != nil {
		return m.executeFn(cmd, args...)
	}
	return &gomysql.Result{}, nil
}

func (m *mockUpstream) UseDB(db string) error {
	if m.useDBFn != nil {
		return m.useDBFn(db)
	}
	return nil
}

func (m *mockUpstream) FieldList(table, wildcard string) ([]*gomysql.Field, error) {
	if m.fieldListFn != nil {
		return m.fieldListFn(table, wildcard)
	}
	return nil, nil
}

func (m *mockUpstream) Prepare(query string) (proxymysql.Stmt, error) {
	if m.prepareFn != nil {
		return m.prepareFn(query)
	}
	return nil, errors.New("not implemented")
}

func (m *mockUpstream) Close() error {
	if m.closeFn != nil {
		return m.closeFn()
	}
	return nil
}

type mockStmt struct {
	params  int
	columns int
	execFn  func(...any) (*gomysql.Result, error)
}

func (s *mockStmt) Execute(args ...any) (*gomysql.Result, error) {
	if s.execFn != nil {
		return s.execFn(args...)
	}
	return &gomysql.Result{}, nil
}

func (s *mockStmt) Close() error       { return nil }
func (s *mockStmt) ParamNum() int      { return s.params }
func (s *mockStmt) ColumnNum() int     { return s.columns }

type mockLogAdder struct {
	mu   sync.Mutex
	logs []shipper.QueryLog
}

func (m *mockLogAdder) Add(q shipper.QueryLog) {
	m.mu.Lock()
	m.logs = append(m.logs, q)
	m.mu.Unlock()
}

func (m *mockLogAdder) all() []shipper.QueryLog {
	m.mu.Lock()
	defer m.mu.Unlock()
	return append([]shipper.QueryLog(nil), m.logs...)
}

// --- tests ---

func TestHandleQuery_ForwardsAndLogs(t *testing.T) {
	result := &gomysql.Result{AffectedRows: 3}
	upstream := &mockUpstream{
		executeFn: func(cmd string, _ ...any) (*gomysql.Result, error) {
			if cmd != "SELECT 1" {
				t.Errorf("unexpected query forwarded: %q", cmd)
			}
			return result, nil
		},
	}
	adder := &mockLogAdder{}
	h := proxymysql.New(upstream, adder)

	got, err := h.HandleQuery("SELECT 1")
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if got != result {
		t.Fatal("handler returned different result than upstream")
	}

	logs := adder.all()
	if len(logs) != 1 {
		t.Fatalf("expected 1 log entry, got %d", len(logs))
	}
	if logs[0].Query != "SELECT 1" {
		t.Errorf("wrong query logged: %q", logs[0].Query)
	}
	if logs[0].RowsAffected != 3 {
		t.Errorf("wrong rows_affected: %d", logs[0].RowsAffected)
	}
	if logs[0].Error != "" {
		t.Errorf("expected no error, got %q", logs[0].Error)
	}
	if logs[0].DurationMs < 0 {
		t.Errorf("negative duration: %d", logs[0].DurationMs)
	}
}

func TestHandleQuery_LogsUpstreamError(t *testing.T) {
	upstreamErr := errors.New("connection reset")
	upstream := &mockUpstream{
		executeFn: func(_ string, _ ...any) (*gomysql.Result, error) {
			return nil, upstreamErr
		},
	}
	adder := &mockLogAdder{}
	h := proxymysql.New(upstream, adder)

	_, err := h.HandleQuery("DELETE FROM secrets")
	if err != upstreamErr {
		t.Fatalf("expected upstream error propagated, got %v", err)
	}

	logs := adder.all()
	if len(logs) != 1 {
		t.Fatalf("expected 1 log entry even on error, got %d", len(logs))
	}
	if logs[0].Error != "connection reset" {
		t.Errorf("expected error logged, got %q", logs[0].Error)
	}
}

func TestHandleQuery_TimestampIsUTC(t *testing.T) {
	before := time.Now().UTC()
	upstream := &mockUpstream{}
	adder := &mockLogAdder{}
	h := proxymysql.New(upstream, adder)

	_, _ = h.HandleQuery("SELECT NOW()")
	after := time.Now().UTC()

	logs := adder.all()
	if len(logs) != 1 {
		t.Fatalf("expected 1 log, got %d", len(logs))
	}
	ts := logs[0].Timestamp
	if ts.Location() != time.UTC {
		t.Errorf("expected UTC timestamp, got %v", ts.Location())
	}
	if ts.Before(before) || ts.After(after) {
		t.Errorf("timestamp %v not between %v and %v", ts, before, after)
	}
}

func TestUseDB_Delegates(t *testing.T) {
	called := ""
	upstream := &mockUpstream{
		useDBFn: func(db string) error { called = db; return nil },
	}
	h := proxymysql.New(upstream, &mockLogAdder{})

	if err := h.UseDB("mydb"); err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if called != "mydb" {
		t.Errorf("UseDB not forwarded: got %q", called)
	}
}

func TestHandleStmtPrepare_ReturnsParamAndColumnCount(t *testing.T) {
	stmt := &mockStmt{params: 2, columns: 3}
	upstream := &mockUpstream{
		prepareFn: func(_ string) (proxymysql.Stmt, error) { return stmt, nil },
	}
	h := proxymysql.New(upstream, &mockLogAdder{})

	params, columns, ctx, err := h.HandleStmtPrepare("SELECT ? + ?")
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if params != 2 {
		t.Errorf("expected 2 params, got %d", params)
	}
	if columns != 3 {
		t.Errorf("expected 3 columns, got %d", columns)
	}
	if ctx == nil {
		t.Fatal("expected non-nil context")
	}
}

func TestHandleStmtExecute_LogsAndForwards(t *testing.T) {
	result := &gomysql.Result{AffectedRows: 1}
	stmt := &mockStmt{
		params:  1,
		columns: 0,
		execFn: func(args ...any) (*gomysql.Result, error) {
			return result, nil
		},
	}
	adder := &mockLogAdder{}
	h := proxymysql.New(&mockUpstream{}, adder)

	got, err := h.HandleStmtExecute(stmt, "INSERT INTO t VALUES (?)", []any{42})
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if got != result {
		t.Fatal("wrong result returned")
	}

	logs := adder.all()
	if len(logs) != 1 {
		t.Fatalf("expected 1 log, got %d", len(logs))
	}
	// Bound args are appended so the audit log captures the resolved values.
	if logs[0].Query != "INSERT INTO t VALUES (?) -- args: [42]" {
		t.Errorf("wrong query logged: %q", logs[0].Query)
	}
}

func TestHandleStmtExecute_InvalidContext(t *testing.T) {
	h := proxymysql.New(&mockUpstream{}, &mockLogAdder{})
	_, err := h.HandleStmtExecute("not-a-stmt", "SELECT 1", nil)
	if err == nil {
		t.Fatal("expected error for invalid context")
	}
}

func TestHandleStmtClose_ValidContext(t *testing.T) {
	closed := false
	stmt := &mockStmt{}
	stmt2 := &closingStmt{mockStmt: *stmt, onClose: func() { closed = true }}
	h := proxymysql.New(&mockUpstream{}, &mockLogAdder{})

	if err := h.HandleStmtClose(stmt2); err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if !closed {
		t.Fatal("expected stmt.Close() to be called")
	}
}

func TestHandleStmtClose_InvalidContextNoError(t *testing.T) {
	h := proxymysql.New(&mockUpstream{}, &mockLogAdder{})
	if err := h.HandleStmtClose("garbage"); err != nil {
		t.Fatalf("expected nil for invalid context, got %v", err)
	}
}

func TestHandleOtherCommand_ReturnsError(t *testing.T) {
	h := proxymysql.New(&mockUpstream{}, &mockLogAdder{})
	err := h.HandleOtherCommand(0x42, nil)
	if err == nil {
		t.Fatal("expected error for unsupported command")
	}
}

// closingStmt tracks whether Close was called.
type closingStmt struct {
	mockStmt
	onClose func()
}

func (s *closingStmt) Close() error {
	s.onClose()
	return nil
}

// --- TC-17: Oversized prepared statement args are truncated at rune boundaries ---

// TestHandleStmtExecute_LongArg_TruncatedAtRuneBoundary verifies that a
// prepared statement argument longer than maxArgValueLen runes is truncated to
// exactly maxArgValueLen runes with a "…" suffix, never splitting a multi-byte
// UTF-8 character mid-sequence.
func TestHandleStmtExecute_LongArg_TruncatedAtRuneBoundary(t *testing.T) {
	// "あ" is U+3042: 3 bytes in UTF-8, 1 rune. 300 of them = 900 bytes, 300 runes.
	// maxArgValueLen = 256, so the logged value must be 256 runes + "…".
	const runeChar = "あ"
	const totalRunes = 300
	longArg := strings.Repeat(runeChar, totalRunes)

	stmt := &mockStmt{
		params: 1,
		execFn: func(args ...any) (*gomysql.Result, error) {
			return &gomysql.Result{AffectedRows: 1}, nil
		},
	}
	adder := &mockLogAdder{}
	h := proxymysql.New(&mockUpstream{}, adder)

	_, err := h.HandleStmtExecute(stmt, "INSERT INTO t (name) VALUES (?)", []any{longArg})
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}

	logs := adder.all()
	if len(logs) != 1 {
		t.Fatalf("expected 1 log, got %d", len(logs))
	}
	q := logs[0].Query

	// Must be valid UTF-8 — truncation must not split multi-byte runes.
	if !utf8.ValidString(q) {
		t.Errorf("logged query contains invalid UTF-8 after truncation: %q", q)
	}

	// Must contain the ellipsis that signals truncation occurred.
	if !strings.Contains(q, "…") {
		t.Errorf("expected truncation ellipsis in logged query, got: %q", q)
	}

	// Verify exactly maxArgValueLen (256) runes of the original character
	// precede the ellipsis.
	const maxLen = 256
	expectedTruncated := strings.Repeat(runeChar, maxLen) + "…"
	if !strings.Contains(q, expectedTruncated) {
		t.Errorf("expected %d runes of %q followed by …\ngot query: %q", maxLen, runeChar, q)
	}

	// The full 300-rune string must NOT appear — confirm truncation happened.
	if strings.Contains(q, strings.Repeat(runeChar, totalRunes)) {
		t.Errorf("full %d-rune string appears in log — truncation did not occur", totalRunes)
	}
}

// TestHandleStmtExecute_ShortArg_NotTruncated verifies that args within the
// limit are logged verbatim (no spurious truncation or ellipsis).
func TestHandleStmtExecute_ShortArg_NotTruncated(t *testing.T) {
	arg := strings.Repeat("あ", 10) // well within the 256-rune limit

	stmt := &mockStmt{
		params: 1,
		execFn: func(args ...any) (*gomysql.Result, error) {
			return &gomysql.Result{}, nil
		},
	}
	adder := &mockLogAdder{}
	h := proxymysql.New(&mockUpstream{}, adder)

	_, _ = h.HandleStmtExecute(stmt, "SELECT ?", []any{arg})

	logs := adder.all()
	if len(logs) != 1 {
		t.Fatalf("expected 1 log, got %d", len(logs))
	}
	if strings.Contains(logs[0].Query, "…") {
		t.Errorf("unexpected truncation ellipsis for short arg: %q", logs[0].Query)
	}
	if !strings.Contains(logs[0].Query, arg) {
		t.Errorf("short arg not present verbatim in log: %q", logs[0].Query)
	}
}
