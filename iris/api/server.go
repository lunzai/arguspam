package api

import (
	"crypto/subtle"
	"net/http"

	"github.com/arguspam/iris/proxy"
)

// managerIface is the subset of proxy.Manager used by the HTTP handlers.
// Defined as an interface to allow test doubles.
type managerIface interface {
	Start(proxy.StartRequest) (int, error)
	Stop(string) (proxy.StopResponse, error)
	Status(string) (proxy.StatusResponse, error)
	ActiveSessionCount() int
}

// Server is the HTTP management API for the proxy service.
type Server struct {
	secret  string
	manager managerIface
	mux     *http.ServeMux
}

func NewServer(secret string, manager *proxy.Manager) *Server {
	return newServer(secret, manager)
}

// NewServerWithManager constructs a Server with an explicit managerIface.
// Intended for use in tests with a mock manager.
func NewServerWithManager(secret string, manager managerIface) *Server {
	return newServer(secret, manager)
}

func newServer(secret string, manager managerIface) *Server {
	if secret == "" {
		panic("api: empty internal secret is not permitted")
	}
	s := &Server{
		secret:  secret,
		manager: manager,
		mux:     http.NewServeMux(),
	}
	s.routes()
	return s
}

func (s *Server) routes() {
	// /health is unauthenticated (used by Docker health checks).
	s.mux.HandleFunc("GET /health", s.handleHealth)

	// All management endpoints require the shared secret.
	s.mux.HandleFunc("POST /proxy", s.auth(s.handleStart))
	s.mux.HandleFunc("DELETE /proxy/{id}", s.auth(s.handleStop))
	s.mux.HandleFunc("GET /proxy/{id}", s.auth(s.handleStatus))
}

// auth middleware validates the X-Proxy-Secret header using constant-time
// comparison to prevent timing attacks.
func (s *Server) auth(next http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		provided := r.Header.Get("X-Proxy-Secret")
		if subtle.ConstantTimeCompare([]byte(provided), []byte(s.secret)) != 1 {
			jsonCode(w, http.StatusUnauthorized, map[string]string{"error": "unauthorized"})
			return
		}
		next(w, r)
	}
}

func (s *Server) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	s.mux.ServeHTTP(w, r)
}
