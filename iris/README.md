# iris — ArgusPAM Database Proxy Service

iris is a Go service that transparently proxies MySQL connections for PAM sessions. It intercepts every query a JIT user runs, logs it in real time, and ships the audit trail back to the Laravel API — without requiring any changes to the target database.

For the full architectural motivation and design decisions see [`docs/proxy-audit-architecture.md`](docs/proxy-audit-architecture.md).

---

## Table of Contents

1. [Setup](#setup)
   - [Local Mac Setup (ServBay, no Docker)](#local-mac-setup-servbay-no-docker)
   - [Environment Variables](#environment-variables)
   - [Building](#building)
   - [Docker](#docker)
   - [Docker Compose](#docker-compose)
2. [Code Explanation](#code-explanation)
3. [Usage — HTTP API](#usage--http-api)
4. [Integration with Laravel](#integration-with-laravel)

---

## Setup

### Local Mac Setup (ServBay, no Docker)

This is the recommended setup for local development. The Laravel API and MySQL are managed by ServBay; the web frontend runs via `npm run dev`; iris runs as a plain Go binary in a terminal.

#### Prerequisites

- [Go](https://go.dev/dl/) 1.24+ installed (`go version` to verify)
- ServBay running with:
  - MySQL started
  - The Laravel API site active and reachable (e.g. `http://arguspam.test`)
- Web frontend running (`npm run dev` in the `web/` directory)

#### 1. Create a `.env.local` file for iris

Inside the `iris/` directory, create a file called `.env.local` to hold your local config. **Do not commit this file.**

```bash
# iris/.env.local

# Point to your ServBay Laravel site URL (no trailing slash)
ARGUSPAM_API_URL=http://arguspam.test

# Must match PROXY_INTERNAL_SECRET in your Laravel .env
ARGUSPAM_INTERNAL_SECRET=dev-secret-change-me

# Management API port — keep this away from ServBay's ports
LISTEN_ADDR=:8080

# Proxy port range — SQL clients connect on these ports
PORT_RANGE_START=15000
PORT_RANGE_END=15009

# Flush every 5 seconds or every 10 queries in dev (lower than prod for faster feedback)
FLUSH_INTERVAL_SECONDS=5
FLUSH_BATCH_SIZE=10

# Bind only to localhost in dev — no need to expose to the network
PROXY_BIND_ADDR=127.0.0.1
```

`PORT_RANGE_START=15000 PORT_RANGE_END=15009` gives you 10 concurrent sessions locally, which is plenty for development. Adjust if you need more.

#### 2. Add the matching secret to Laravel

In your Laravel `.env` (managed by ServBay), add:

```dotenv
PROXY_INTERNAL_SECRET=dev-secret-change-me
PROXY_SERVICE_URL=http://127.0.0.1:8080
```

Both values must match what is in `iris/.env.local`.

#### 3. Build iris

```bash
cd iris/
go build -o iris .
```

You only need to rebuild when Go source files change. `go run .` also works if you prefer to skip the build step.

#### 4. Run iris

Source the env file and start the binary:

```bash
cd iris/
export $(grep -v '^#' .env.local | xargs) && ./iris
```

Or as a one-liner without permanently exporting:

```bash
cd iris/
env $(grep -v '^#' .env.local | xargs) ./iris
```

You should see:

```
iris proxy service starting on :8080 (ports 15000-15009, bind 127.0.0.1)
```

Leave this terminal open. iris runs in the foreground — `Ctrl+C` for a clean shutdown (SIGINT triggers graceful flush of all active sessions).

#### 5. Verify everything is wired up

```bash
# Health check
curl http://127.0.0.1:8080/health
# → {"status":"ok"}

# Quick smoke test — start a session pointing to ServBay's MySQL
curl -s -X POST http://127.0.0.1:8080/proxy \
  -H "X-Proxy-Secret: dev-secret-change-me" \
  -H "Content-Type: application/json" \
  -d '{"session_id":"smoke-test","dbms":"mysql","jit_username":"<jit_user>","jit_password":"<jit_pass>","db_host":"127.0.0.1","db_port":3306}'
# → {"port":15009}

# Connect through the proxy
mysql -h 127.0.0.1 -P 15009 -u <jit_user> -p<jit_pass>

# Clean up
curl -s -X DELETE http://127.0.0.1:8080/proxy/smoke-test \
  -H "X-Proxy-Secret: dev-secret-change-me"
# → {"flushed":true,"total_queries":0}
```

#### Running all three services together

| Service | How to run | Port |
|---|---|---|
| MySQL | ServBay (always on) | 3306 |
| Laravel API | ServBay site | 80 / 443 (via `arguspam.test`) |
| Web frontend | `npm run dev` in `web/` | 5173 (Vite default) |
| iris proxy | `env $(grep -v '^#' iris/.env.local \| xargs) ./iris` | 8080 (mgmt), 15000–15009 (proxy) |

Each runs in its own terminal tab. No Docker required.

#### Rebuilding after code changes

```bash
cd iris/
go build -o iris . && env $(grep -v '^#' .env.local | xargs) ./iris
```

Or if you prefer to avoid the build step during development:

```bash
env $(grep -v '^#' .env.local | xargs) go run .
```

---

### Environment Variables

| Variable | Default | Description |
|---|---|---|
| `LISTEN_ADDR` | `:8080` | Address the HTTP management API binds to |
| `ARGUSPAM_API_URL` | `http://api` | Base URL of the Laravel API (no trailing slash) |
| `ARGUSPAM_INTERNAL_SECRET` | *(required)* | Shared secret for API authentication. Must be set or the service refuses to start. |
| `PORT_RANGE_START` | `15000` | First port in the proxy pool |
| `PORT_RANGE_END` | `16000` | Last port in the proxy pool (inclusive). Defines the max concurrent sessions. |
| `FLUSH_INTERVAL_SECONDS` | `5` | How often the shipper flushes query logs to Laravel, in seconds |
| `FLUSH_BATCH_SIZE` | `50` | Flush immediately when this many queries have accumulated |
| `PROXY_BIND_ADDR` | `0.0.0.0` | Host address that per-session proxy listeners bind to |

`ARGUSPAM_INTERNAL_SECRET` must be a high-entropy random value (256-bit hex recommended). Both iris and Laravel must use the same value.

### Building

```bash
# From the iris/ directory
go build -o iris .

# Or directly
go run .
```

### Docker

The service ships with a multi-stage Dockerfile that produces a minimal distroless image:

```bash
docker build -t iris .
docker run --rm \
  -e ARGUSPAM_INTERNAL_SECRET=<secret> \
  -e ARGUSPAM_API_URL=http://api \
  -p 8080:8080 \
  -p 15000-16000:15000-16000 \
  iris
```

### Docker Compose

Add to your `docker-compose.yml`:

```yaml
proxy-service:
  build:
    context: ./iris
  restart: unless-stopped
  ports:
    - "${PORT_RANGE_START:-15000}-${PORT_RANGE_END:-16000}:${PORT_RANGE_START:-15000}-${PORT_RANGE_END:-16000}"
  environment:
    ARGUSPAM_API_URL: http://api
    ARGUSPAM_INTERNAL_SECRET: ${ARGUSPAM_INTERNAL_SECRET}
    PORT_RANGE_START: ${PORT_RANGE_START:-15000}
    PORT_RANGE_END: ${PORT_RANGE_END:-16000}
    FLUSH_INTERVAL_SECONDS: 5
    FLUSH_BATCH_SIZE: 50
    PROXY_BIND_ADDR: "0.0.0.0"
  networks:
    - internal
  depends_on:
    - api
  healthcheck:
    test: ["CMD", "wget", "-qO-", "http://localhost:8080/health"]
    interval: 30s
    timeout: 5s
    retries: 3
```

Port 8080 (management API) must **not** be mapped to the host — it must be reachable only from the internal Docker network. The port range is the only thing exposed to the outside for SQL client connections.

### Running Tests

```bash
go test ./...
```

---

## Code Explanation

### Package Structure

```
iris/
├── main.go              — entry point: config, wiring, graceful shutdown
├── api/
│   ├── server.go        — HTTP server, route registration, auth middleware
│   └── handlers.go      — request parsing and response for each endpoint
├── pool/
│   └── ports.go         — thread-safe port pool (Acquire / Release / ReleaseFor)
├── proxy/
│   ├── manager.go       — session registry, Start / Stop / StopAll lifecycle
│   ├── session.go       — per-session TCP listener, accept loop, MySQL handshake
│   └── mysql/
│       ├── handler.go   — MySQL server handler: query interception and logging
│       └── upstream.go  — upstream MySQL client connection (real database)
└── shipper/
    └── shipper.go       — batches query logs, ships them to Laravel via HTTP
```

### Component Overview

```
SQL Client
    │  (MySQL wire protocol, JIT credentials)
    ▼
[ Session — TCP listener on proxy port ]
    │  accepts one connection at a time
    ▼
[ mysql.Handler — go-mysql server handler ]
    │  intercepts COM_QUERY, COM_STMT_PREPARE, COM_STMT_EXECUTE
    │  logs each query to Shipper
    ▼
[ mysql.Upstream — go-mysql client connection ]
    │  forwards query to real database using same JIT credentials
    ▼
Real Database

[ Shipper ] ← receives query logs from Handler
    │  buffers, batches (by count or interval)
    ▼
Laravel API  POST /internal/proxy/sessions/{id}/logs
```

### Port Pool (`pool/ports.go`)

Manages a fixed range of ports available for sessions.

- `New(start, end)` — initialises the pool with all ports in range
- `Acquire(sessionID)` — claims a port and records the owning session; returns `ErrPoolExhausted` if none are free
- `Release(port)` — unconditional release
- `ReleaseFor(port, sessionID)` — ownership-verified release; no-op if the port belongs to a different session (prevents session A from accidentally freeing session B's port)

All operations are protected by a mutex and safe for concurrent use.

### Manager (`proxy/manager.go`)

Owns the session registry and coordinates the full session lifecycle.

The `slot` struct is the key concurrency primitive:

```
Start() call:
  1. Insert empty slot{} under lock  ← reserves the session ID atomically
  2. Acquire port from pool          ← outside the lock (pool has its own mutex)
  3. Store port in slot under lock   ← Stop() can now release the port even if Start() hasn't finished
  4. Create Session + start listener ← outside the lock
  5. Promote slot.sess under lock    ← Stop() can now stop the session

Stop() call:
  - Deletes the slot under lock
  - If slot.port != 0: releases the port
  - If slot.sess != nil: calls sess.stop()
```

This design means that a `Stop()` arriving at any point during `Start()` — even between port acquisition and session start — will always clean up correctly, with no port leaks.

`StopAll(ctx)` runs all stops concurrently in goroutines and waits up to the context deadline, allowing graceful shutdown to bound its total time.

### Session (`proxy/session.go`)

Each session owns:
- A TCP listener on its allocated port
- A Shipper for shipping query logs
- An accept loop goroutine

**Accept loop behaviour:**

- Accepts one client connection at a time (PAM sessions are single-user)
- If the client disconnects and reconnects within the session window, the loop accepts the new connection on the same port
- Transient OS errors (`EMFILE`, `ENFILE`, `ENOBUFS`) are retried with a 100ms back-off; fatal errors cause the loop to exit
- A stop/Accept race is handled by checking `s.stopped` inside `clientMu` before assigning `clientConn`

**Upstream dial:**

The upstream MySQL dial is wired to `s.stopped` so that calling `stop()` immediately cancels an in-progress dial rather than letting it block for the full 15-second timeout:

```go
dialCtx, cancelDial := context.WithCancel(context.Background())
go func() {
    select {
    case <-s.stopped: cancelDial()
    case <-dialCtx.Done():
    }
}()
upstream, err := proxymysql.Dial(dialCtx, ...)
```

**Shutdown sequence:**

`stop()` is idempotent via `sync.Once`. It:
1. Closes `s.stopped` (signals all goroutines)
2. Closes the TCP listener (unblocks `Accept()`)
3. Closes the active client connection
4. Waits for the accept loop goroutine to exit
5. Calls `shipper.Stop()` (final flush)

The loopWG wait before `shipper.Stop()` ensures no `Add()` calls can race the shipper shutdown.

### MySQL Handler (`proxy/mysql/handler.go`)

Implements the `server.Handler` interface from `go-mysql`. One handler is created per client connection.

| Method | What it does |
|---|---|
| `UseDB` | Forwards `USE <db>` to upstream |
| `HandleQuery` | Executes the query upstream, logs it with duration and affected rows |
| `HandleFieldList` | Forwards `SHOW COLUMNS` / field list requests |
| `HandleStmtPrepare` | Prepares the statement on upstream; returns param/column counts |
| `HandleStmtExecute` | Executes prepared statement; appends bound args to the audit log |
| `HandleStmtClose` | Closes prepared statement on upstream |
| `HandleOtherCommand` | Returns an unsupported-command error for anything else |

Prepared statement arguments are appended to the logged query text as `-- args: [...]` so the audit log captures resolved values, not just placeholder syntax. Each argument value is truncated to 256 runes (rune-boundary safe) to prevent large BLOBs from producing unbounded log entries. **Note: bound values including potentially sensitive data are intentionally logged for PAM audit purposes — ensure log storage is access-controlled.**

### Shipper (`shipper/shipper.go`)

Buffers query logs in memory and delivers them to Laravel in batches.

**Design — single sender goroutine:**

All HTTP sends go through one goroutine (`sender()`). This guarantees batches are delivered strictly in the order they were flushed, with no concurrent sends racing each other.

```
Add() → appends to buffer
         if len(buffer) >= batchSize → signal flushCh

sender():
  select:
    ticker fires → drainAndSend() → sendWithRetry()
    flushCh fires → drainAndSend() → sendWithRetry()
    done closes → drainAndSendFinal() → single attempt, 5s deadline → return
```

**Retry policy:**

- Normal sends: up to 3 attempts, exponential back-off starting at 500ms, 10s per-attempt timeout
- Back-off sleep is interruptible via `s.done` so shutdown is never delayed by a retry sleep
- Shutdown flush (`drainAndSendFinal`): single attempt, 5s deadline, no retries
- Permanent failure is logged with the count of queries lost

**HTTP request format:**

```
POST {ARGUSPAM_API_URL}/internal/proxy/sessions/{sessionID}/logs
Content-Type: application/json
X-Proxy-Secret: <shared secret>

{
  "queries": [
    {
      "timestamp": "2026-01-01T10:42:03.123456Z",
      "query": "SELECT * FROM users LIMIT 10",
      "duration_ms": 4,
      "rows_affected": 10,
      "error": ""
    }
  ]
}
```

### Graceful Shutdown (`main.go`)

On `SIGTERM` or `SIGINT`:

1. HTTP server stops accepting new requests (`httpSrv.Shutdown` with 30s deadline)
2. In-flight HTTP requests are drained
3. `manager.StopAll(shutdownCtx)` stops all active sessions concurrently
4. Each session's shipper performs a final flush before the process exits

---

## Usage — HTTP API

The management API listens on `LISTEN_ADDR` (default `:8080`). It is internal-only and must not be exposed publicly.

All endpoints except `/health` require the `X-Proxy-Secret` header matching `ARGUSPAM_INTERNAL_SECRET`. Missing or wrong secret returns `401 Unauthorized`.

---

### `GET /health`

Health check. No authentication required. Used by Docker health checks.

**Response `200 OK`:**
```json
{ "status": "ok" }
```

---

### `POST /proxy`

Start a new proxy session. Allocates a port from the pool and starts a TCP listener.

**Headers:**
```
X-Proxy-Secret: <secret>
Content-Type: application/json
```

**Request body:**
```json
{
  "session_id": "01950d4e-1234-7abc-8def-abcdef012345",
  "dbms": "mysql",
  "jit_username": "pam_abc123",
  "jit_password": "s3cr3t!",
  "db_host": "db.company.com",
  "db_port": 3306
}
```

| Field | Type | Required | Notes |
|---|---|---|---|
| `session_id` | string | yes | Unique session UUID |
| `dbms` | string | yes | Must be `"mysql"` (only supported DBMS) |
| `jit_username` | string | yes | JIT account username |
| `jit_password` | string | yes | JIT account password |
| `db_host` | string | yes | Target database hostname |
| `db_port` | integer | yes | Target database port (1–65535) |

**Response `201 Created`:**
```json
{ "port": 15001 }
```

**Error responses:**

| Status | Condition |
|---|---|
| `400 Bad Request` | Missing fields, invalid port, or unsupported DBMS |
| `409 Conflict` | Session ID already exists |
| `503 Service Unavailable` | Port pool exhausted |

---

### `DELETE /proxy/{id}`

Stop a session. Performs a final log flush, tears down the listener, and releases the port back to the pool.

**Headers:**
```
X-Proxy-Secret: <secret>
```

**Response `200 OK`:**
```json
{
  "flushed": true,
  "total_queries": 142
}
```

`flushed` is always `true` — iris always attempts the final flush; delivery failures are logged on the iris side but do not block teardown.

**Error responses:**

| Status | Condition |
|---|---|
| `404 Not Found` | No session with this ID |

---

### `GET /proxy/{id}`

Get the current status of a session.

**Headers:**
```
X-Proxy-Secret: <secret>
```

**Response `200 OK`:**
```json
{
  "active": true,
  "port": 15001,
  "query_count": 87,
  "client_connected": true,
  "session_started_at": "2026-01-01T10:00:00Z"
}
```

| Field | Description |
|---|---|
| `active` | `true` once the session is fully started |
| `port` | Allocated proxy port |
| `query_count` | Queries intercepted so far (includes in-buffer, not yet flushed) |
| `client_connected` | Whether a SQL client is currently connected to the proxy port |
| `session_started_at` | When the session was created (UTC) |

**Error responses:**

| Status | Condition |
|---|---|
| `404 Not Found` | No session with this ID |

---

## Integration with Laravel

### What iris calls

iris makes one type of outbound HTTP call — log delivery:

```
POST {ARGUSPAM_API_URL}/internal/proxy/sessions/{session_id}/logs
Content-Type: application/json
X-Proxy-Secret: <ARGUSPAM_INTERNAL_SECRET>

{
  "queries": [
    {
      "timestamp": "2026-01-01T10:42:03.123456789Z",
      "query": "SELECT id, email FROM users WHERE active = 1",
      "duration_ms": 4,
      "rows_affected": 250,
      "error": ""
    },
    {
      "timestamp": "2026-01-01T10:42:11.456Z",
      "query": "UPDATE users SET last_login = NOW() WHERE id = ? -- args: [42]",
      "duration_ms": 8,
      "rows_affected": 1,
      "error": ""
    }
  ]
}
```

iris expects a `2xx` response. Any `4xx` or `5xx` response is treated as a delivery failure and retried (up to 3 times for normal sends). Laravel should return `200` or `204` on success.

### What Laravel must implement

#### 1. Log ingestion endpoint

```
POST /internal/proxy/sessions/{session}/logs
```

Protected by `X-Proxy-Secret` header middleware. Receives batches of `QueryLog` objects and stores them as `SessionAudit` records.

The endpoint must be idempotent or tolerant of duplicate delivery — iris retries on transient failures and may occasionally deliver the same batch twice.

#### 2. Start a proxy session (calling iris)

In the listener for `SessionStarted` (after the JIT account is created):

```php
$port = $proxyService->startSession($session);
$session->update(['proxy_port' => $port]);
```

Call `POST /proxy` on iris, store the returned port on the session record, and include the proxy host/port/credentials in the session API resource so the frontend can show the connection card.

#### 3. Stop a proxy session (calling iris)

In the listeners for `SessionEnded`, `SessionTerminated`, `SessionExpired`:

```php
$proxyService->stopSession($session);
// Then revoke the JIT account as normal
```

Call `DELETE /proxy/{session_id}` on iris **before** revoking the JIT account. This ensures iris can still flush any buffered queries before the JIT credentials become invalid.

### Shared Secret

Both sides must use the same value for `ARGUSPAM_INTERNAL_SECRET` (iris) and `PROXY_INTERNAL_SECRET` (Laravel). Generate a cryptographically random value:

```bash
openssl rand -hex 32
```

Set it in both `.env` files. Never commit it to version control.

### Security Notes

- The iris management API (port 8080) must only be reachable from the Laravel container. Map only the proxy port range to the host.
- The log ingestion endpoint (`/internal/proxy/...`) must be behind `X-Proxy-Secret` middleware on the Laravel side.
- iris never logs the upstream database host/port or the JIT password. Bound prepared statement values are logged (audit requirement) — ensure log storage is access-controlled.
