package api

import (
	"encoding/json"
	"errors"
	"log"
	"net/http"

	"github.com/arguspam/iris/pool"
	"github.com/arguspam/iris/proxy"
)

func (s *Server) handleHealth(w http.ResponseWriter, r *http.Request) {
	jsonOK(w, map[string]string{"status": "ok"})
}

func (s *Server) handleStart(w http.ResponseWriter, r *http.Request) {
	var body struct {
		SessionID   string `json:"session_id"`
		DBMS        string `json:"dbms"`
		JitUsername string `json:"jit_username"`
		JitPassword string `json:"jit_password"`
		DBHost      string `json:"db_host"`
		DBPort      int    `json:"db_port"`
	}
	r.Body = http.MaxBytesReader(w, r.Body, 4096)
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		jsonErr(w, http.StatusBadRequest, "invalid request body")
		return
	}
	if body.SessionID == "" || body.DBMS == "" || body.JitUsername == "" ||
		body.JitPassword == "" || body.DBHost == "" || body.DBPort < 1 || body.DBPort > 65535 {
		jsonErr(w, http.StatusBadRequest, "missing required fields")
		return
	}
	if body.DBMS != "mysql" {
		jsonErr(w, http.StatusBadRequest, "unsupported dbms: only mysql is supported")
		return
	}

	port, err := s.manager.Start(proxy.StartRequest{
		SessionID:   body.SessionID,
		DBMS:        body.DBMS,
		JitUsername: body.JitUsername,
		JitPassword: body.JitPassword,
		DBHost:      body.DBHost,
		DBPort:      body.DBPort,
	})
	if err != nil {
		if errors.Is(err, pool.ErrPoolExhausted) {
			jsonErr(w, http.StatusServiceUnavailable, "port pool exhausted")
			return
		}
		log.Printf("[api] start session %q failed: %v", body.SessionID, err)
		jsonErr(w, http.StatusInternalServerError, "failed to start session")
		return
	}

	jsonCode(w, http.StatusCreated, map[string]any{"port": port})
}

func (s *Server) handleStop(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	resp, err := s.manager.Stop(id)
	if err != nil {
		if errors.Is(err, proxy.ErrSessionNotFound) {
			jsonErr(w, http.StatusNotFound, "session not found")
			return
		}
		log.Printf("[api] stop session %q failed: %v", id, err)
		jsonErr(w, http.StatusInternalServerError, "failed to stop session")
		return
	}
	jsonOK(w, resp)
}

func (s *Server) handleStatus(w http.ResponseWriter, r *http.Request) {
	id := r.PathValue("id")
	resp, err := s.manager.Status(id)
	if err != nil {
		if errors.Is(err, proxy.ErrSessionNotFound) {
			jsonErr(w, http.StatusNotFound, "session not found")
			return
		}
		log.Printf("[api] status session %q failed: %v", id, err)
		jsonErr(w, http.StatusInternalServerError, "failed to get session status")
		return
	}
	jsonOK(w, resp)
}

func jsonOK(w http.ResponseWriter, v any) {
	jsonCode(w, http.StatusOK, v)
}

func jsonCode(w http.ResponseWriter, code int, v any) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(code)
	_ = json.NewEncoder(w).Encode(v)
}

func jsonErr(w http.ResponseWriter, code int, msg string) {
	jsonCode(w, code, map[string]string{"error": msg})
}
