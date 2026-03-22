package api_test

import (
	"bytes"
	"encoding/json"
	"net/http"
	"net/http/httptest"
	"testing"
	"time"

	"github.com/arguspam/iris/api"
	"github.com/arguspam/iris/pool"
	"github.com/arguspam/iris/proxy"
)

const testSecret = "test-secret-abc"

// mockManager implements the managerIface via the exported NewServerWithManager constructor.
type mockManager struct {
	startFn  func(proxy.StartRequest) (int, error)
	stopFn   func(string) (proxy.StopResponse, error)
	statusFn func(string) (proxy.StatusResponse, error)
	countFn  func() int
}

func (m *mockManager) Start(r proxy.StartRequest) (int, error) {
	if m.startFn != nil {
		return m.startFn(r)
	}
	return 15001, nil
}

func (m *mockManager) Stop(id string) (proxy.StopResponse, error) {
	if m.stopFn != nil {
		return m.stopFn(id)
	}
	return proxy.StopResponse{Flushed: true, TotalQueries: 42}, nil
}

func (m *mockManager) Status(id string) (proxy.StatusResponse, error) {
	if m.statusFn != nil {
		return m.statusFn(id)
	}
	return proxy.StatusResponse{
		Active:           true,
		Port:             15001,
		QueryCount:       10,
		ClientConnected:  true,
		SessionStartedAt: time.Now(),
	}, nil
}

func (m *mockManager) ActiveSessionCount() int {
	if m.countFn != nil {
		return m.countFn()
	}
	return 3
}

func newTestServer(t *testing.T, mgr *mockManager) http.Handler {
	t.Helper()
	return api.NewServerWithManager(testSecret, mgr)
}

// authed adds the shared secret header to a request.
func authed(req *http.Request) *http.Request {
	req.Header.Set("X-Proxy-Secret", testSecret)
	return req
}

// --- auth middleware ---

func TestAuth_MissingSecret_Returns401(t *testing.T) {
	srv := newTestServer(t, &mockManager{})
	for _, tc := range []struct{ method, path string }{
		{http.MethodPost, "/proxy"},
		{http.MethodDelete, "/proxy/sess"},
		{http.MethodGet, "/proxy/sess"},
	} {
		req := httptest.NewRequest(tc.method, tc.path, nil) // no secret
		rec := httptest.NewRecorder()
		srv.ServeHTTP(rec, req)
		if rec.Code != http.StatusUnauthorized {
			t.Errorf("%s %s: expected 401, got %d", tc.method, tc.path, rec.Code)
		}
	}
}

func TestAuth_WrongSecret_Returns401(t *testing.T) {
	srv := newTestServer(t, &mockManager{})
	req := httptest.NewRequest(http.MethodGet, "/proxy/sess", nil)
	req.Header.Set("X-Proxy-Secret", "wrong-secret")
	rec := httptest.NewRecorder()
	srv.ServeHTTP(rec, req)
	if rec.Code != http.StatusUnauthorized {
		t.Fatalf("expected 401, got %d", rec.Code)
	}
}

func TestAuth_HealthIsPublic(t *testing.T) {
	srv := newTestServer(t, &mockManager{})
	req := httptest.NewRequest(http.MethodGet, "/health", nil) // no secret
	rec := httptest.NewRecorder()
	srv.ServeHTTP(rec, req)
	if rec.Code != http.StatusOK {
		t.Fatalf("expected 200, got %d", rec.Code)
	}
}

// --- health ---

func TestHandleHealth(t *testing.T) {
	srv := newTestServer(t, &mockManager{})
	req := httptest.NewRequest(http.MethodGet, "/health", nil)
	rec := httptest.NewRecorder()
	srv.ServeHTTP(rec, req)

	if rec.Code != http.StatusOK {
		t.Fatalf("expected 200, got %d", rec.Code)
	}
	var body map[string]any
	_ = json.NewDecoder(rec.Body).Decode(&body)
	if body["status"] != "ok" {
		t.Errorf("expected status=ok, got %v", body["status"])
	}
	// active_sessions is intentionally absent from the unauthenticated health
	// endpoint to avoid leaking operational metrics if the service is exposed.
	if _, present := body["active_sessions"]; present {
		t.Errorf("active_sessions must not appear in unauthenticated health response")
	}
}

// --- POST /proxy ---

func TestHandleStart_Success(t *testing.T) {
	srv := newTestServer(t, &mockManager{
		startFn: func(r proxy.StartRequest) (int, error) {
			if r.SessionID != "sess-abc" || r.DBMS != "mysql" {
				t.Errorf("unexpected start request: %+v", r)
			}
			return 15001, nil
		},
	})

	body := map[string]any{
		"session_id":   "sess-abc",
		"dbms":         "mysql",
		"jit_username": "pam_user",
		"jit_password": "secret",
		"db_host":      "db.internal",
		"db_port":      3306,
	}
	req := authed(httptest.NewRequest(http.MethodPost, "/proxy", jsonBody(body)))
	req.Header.Set("Content-Type", "application/json")
	rec := httptest.NewRecorder()
	srv.ServeHTTP(rec, req)

	if rec.Code != http.StatusCreated {
		t.Fatalf("expected 201, got %d (body: %s)", rec.Code, rec.Body.String())
	}
	if rec.Header().Get("Content-Type") != "application/json" {
		t.Errorf("expected Content-Type application/json, got %q", rec.Header().Get("Content-Type"))
	}
	var resp map[string]any
	_ = json.NewDecoder(rec.Body).Decode(&resp)
	if resp["port"].(float64) != 15001 {
		t.Errorf("expected port=15001, got %v", resp["port"])
	}
}

func TestHandleStart_UnsupportedDBMS(t *testing.T) {
	srv := newTestServer(t, &mockManager{})
	body := map[string]any{
		"session_id": "s", "dbms": "postgres",
		"jit_username": "u", "jit_password": "p", "db_host": "h", "db_port": 3306,
	}
	req := authed(httptest.NewRequest(http.MethodPost, "/proxy", jsonBody(body)))
	rec := httptest.NewRecorder()
	srv.ServeHTTP(rec, req)
	if rec.Code != http.StatusBadRequest {
		t.Fatalf("expected 400 for unsupported dbms, got %d", rec.Code)
	}
}

func TestHandleStart_MissingFields(t *testing.T) {
	srv := newTestServer(t, &mockManager{})

	cases := []map[string]any{
		{},
		{"session_id": "s"},
		{"session_id": "s", "dbms": "mysql"},
		{"session_id": "s", "dbms": "mysql", "jit_username": "u", "jit_password": "p", "db_host": "h"}, // missing db_port
	}
	for _, body := range cases {
		req := authed(httptest.NewRequest(http.MethodPost, "/proxy", jsonBody(body)))
		rec := httptest.NewRecorder()
		srv.ServeHTTP(rec, req)
		if rec.Code != http.StatusBadRequest {
			t.Errorf("body %v: expected 400, got %d", body, rec.Code)
		}
	}
}

func TestHandleStart_PoolExhausted(t *testing.T) {
	srv := newTestServer(t, &mockManager{
		startFn: func(_ proxy.StartRequest) (int, error) {
			return 0, pool.ErrPoolExhausted
		},
	})

	body := map[string]any{
		"session_id": "s", "dbms": "mysql",
		"jit_username": "u", "jit_password": "p",
		"db_host": "h", "db_port": 3306,
	}
	req := authed(httptest.NewRequest(http.MethodPost, "/proxy", jsonBody(body)))
	rec := httptest.NewRecorder()
	srv.ServeHTTP(rec, req)

	if rec.Code != http.StatusServiceUnavailable {
		t.Fatalf("expected 503, got %d", rec.Code)
	}
}

func TestHandleStart_InvalidJSON(t *testing.T) {
	srv := newTestServer(t, &mockManager{})
	req := authed(httptest.NewRequest(http.MethodPost, "/proxy", bytes.NewBufferString("not-json")))
	rec := httptest.NewRecorder()
	srv.ServeHTTP(rec, req)
	if rec.Code != http.StatusBadRequest {
		t.Fatalf("expected 400, got %d", rec.Code)
	}
}

// --- DELETE /proxy/{id} ---

func TestHandleStop_Success(t *testing.T) {
	srv := newTestServer(t, &mockManager{
		stopFn: func(id string) (proxy.StopResponse, error) {
			if id != "my-session" {
				t.Errorf("expected id='my-session', got %q", id)
			}
			return proxy.StopResponse{Flushed: true, TotalQueries: 99}, nil
		},
	})

	req := authed(httptest.NewRequest(http.MethodDelete, "/proxy/my-session", nil))
	rec := httptest.NewRecorder()
	srv.ServeHTTP(rec, req)

	if rec.Code != http.StatusOK {
		t.Fatalf("expected 200, got %d", rec.Code)
	}
	var resp proxy.StopResponse
	_ = json.NewDecoder(rec.Body).Decode(&resp)
	if !resp.Flushed || resp.TotalQueries != 99 {
		t.Errorf("unexpected response: %+v", resp)
	}
}

func TestHandleStop_NotFound(t *testing.T) {
	srv := newTestServer(t, &mockManager{
		stopFn: func(_ string) (proxy.StopResponse, error) {
			return proxy.StopResponse{}, proxy.ErrSessionNotFound
		},
	})

	req := authed(httptest.NewRequest(http.MethodDelete, "/proxy/ghost", nil))
	rec := httptest.NewRecorder()
	srv.ServeHTTP(rec, req)

	if rec.Code != http.StatusNotFound {
		t.Fatalf("expected 404, got %d", rec.Code)
	}
}

// --- GET /proxy/{id} ---

func TestHandleStatus_Success(t *testing.T) {
	srv := newTestServer(t, &mockManager{
		statusFn: func(id string) (proxy.StatusResponse, error) {
			return proxy.StatusResponse{
				Active:          true,
				Port:            15002,
				QueryCount:      7,
				ClientConnected: false,
			}, nil
		},
	})

	req := authed(httptest.NewRequest(http.MethodGet, "/proxy/my-session", nil))
	rec := httptest.NewRecorder()
	srv.ServeHTTP(rec, req)

	if rec.Code != http.StatusOK {
		t.Fatalf("expected 200, got %d", rec.Code)
	}
	var resp proxy.StatusResponse
	_ = json.NewDecoder(rec.Body).Decode(&resp)
	if resp.Port != 15002 || resp.QueryCount != 7 || resp.ClientConnected {
		t.Errorf("unexpected status response: %+v", resp)
	}
}

func TestHandleStatus_NotFound(t *testing.T) {
	srv := newTestServer(t, &mockManager{
		statusFn: func(_ string) (proxy.StatusResponse, error) {
			return proxy.StatusResponse{}, proxy.ErrSessionNotFound
		},
	})

	req := authed(httptest.NewRequest(http.MethodGet, "/proxy/ghost", nil))
	rec := httptest.NewRecorder()
	srv.ServeHTTP(rec, req)

	if rec.Code != http.StatusNotFound {
		t.Fatalf("expected 404, got %d", rec.Code)
	}
}

// --- helpers ---

func jsonBody(v any) *bytes.Buffer {
	b, _ := json.Marshal(v)
	return bytes.NewBuffer(b)
}
