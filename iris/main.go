package main

import (
	"context"
	"log"
	"net/http"
	"os"
	"os/signal"
	"strconv"
	"syscall"
	"time"

	"github.com/arguspam/iris/api"
	"github.com/arguspam/iris/pool"
	"github.com/arguspam/iris/proxy"
)

type Config struct {
	ListenAddr     string
	ArgusURL       string
	InternalSecret string
	PortStart      int
	PortEnd        int
	FlushInterval  time.Duration
	FlushBatchSize int
	ProxyBindAddr  string // host/IP that proxy session listeners bind to
}

func configFromEnv() Config {
	portStart := mustAtoi("PORT_RANGE_START", getEnv("PORT_RANGE_START", "15000"))
	portEnd := mustAtoi("PORT_RANGE_END", getEnv("PORT_RANGE_END", "16000"))
	flushInterval := mustAtoi("FLUSH_INTERVAL_SECONDS", getEnv("FLUSH_INTERVAL_SECONDS", "5"))
	flushBatchSize := mustAtoi("FLUSH_BATCH_SIZE", getEnv("FLUSH_BATCH_SIZE", "50"))

	return Config{
		ListenAddr:     getEnv("LISTEN_ADDR", ":8080"),
		ArgusURL:       getEnv("ARGUSPAM_API_URL", "http://api"),
		InternalSecret: getEnv("ARGUSPAM_INTERNAL_SECRET", ""),
		PortStart:      portStart,
		PortEnd:        portEnd,
		FlushInterval:  time.Duration(flushInterval) * time.Second,
		FlushBatchSize: flushBatchSize,
		ProxyBindAddr:  getEnv("PROXY_BIND_ADDR", "0.0.0.0"),
	}
}

// getEnv returns the environment variable value for key, or fallback if the
// variable is not set. Uses os.LookupEnv so that an explicitly empty value
// (VAR="") is honoured rather than silently replaced with the fallback.
func getEnv(key, fallback string) string {
	if v, ok := os.LookupEnv(key); ok {
		return v
	}
	return fallback
}

// mustAtoi parses val as an integer and calls log.Fatal on parse failure,
// preventing the service from starting with a silent zero/garbage config value.
func mustAtoi(name, val string) int {
	n, err := strconv.Atoi(val)
	if err != nil {
		log.Fatalf("%s must be an integer, got %q: %v", name, val, err)
	}
	return n
}

func main() {
	cfg := configFromEnv()

	if cfg.InternalSecret == "" {
		log.Fatal("ARGUSPAM_INTERNAL_SECRET must be set")
	}

	portPool := pool.New(cfg.PortStart, cfg.PortEnd)
	manager := proxy.NewManager(portPool, cfg.ArgusURL, cfg.InternalSecret, cfg.ProxyBindAddr, cfg.FlushInterval, cfg.FlushBatchSize)

	httpSrv := &http.Server{
		Addr:              cfg.ListenAddr,
		Handler:           api.NewServer(cfg.InternalSecret, manager),
		ReadHeaderTimeout: 5 * time.Second,
		ReadTimeout:       10 * time.Second,
		WriteTimeout:      10 * time.Second,
		IdleTimeout:       60 * time.Second,
	}

	// Graceful shutdown: SIGTERM/SIGINT drains in-flight HTTP requests then
	// stops all active proxy sessions (which flushes their shipper buffers).
	ctx, stop := signal.NotifyContext(context.Background(), syscall.SIGTERM, syscall.SIGINT)
	defer stop()

	go func() {
		log.Printf("iris proxy service starting on %s (ports %d-%d, bind %s)",
			cfg.ListenAddr, cfg.PortStart, cfg.PortEnd, cfg.ProxyBindAddr)
		if err := httpSrv.ListenAndServe(); err != nil && err != http.ErrServerClosed {
			log.Fatalf("http server: %v", err)
		}
	}()

	<-ctx.Done()
	log.Println("shutting down...")

	shutdownCtx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()

	if err := httpSrv.Shutdown(shutdownCtx); err != nil {
		log.Printf("http shutdown error: %v", err)
	}

	manager.StopAll(shutdownCtx)
	log.Println("shutdown complete")
}
