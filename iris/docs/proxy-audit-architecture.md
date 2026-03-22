# Database Proxy Audit Architecture

## Overview

This document details the plan to replace the current `general_log`-based query auditing with a purpose-built database proxy. The proxy intercepts and logs every query a JIT user runs during a PAM session, providing 100% complete audit capture without requiring any configuration changes on the target database.

---

## Problem Statement

### Current approach

The current audit flow reads MySQL's `general_log` at session termination:

```
Session ends
    → retrieveUserQueryLogs() reads mysql.general_log filtered by JIT username
    → stores to session_audits
    → OpenAI analysis runs
```

### Why this fails

| Requirement | Current approach |
|---|---|
| No production DB config changes | **Fails** — requires `general_log = ON` globally |
| Multi-DBMS support | **Partial** — each DBMS has a different log mechanism |
| 100% query capture | **Fails** — general_log must be on before session starts; late enable misses queries |
| Low barrier to entry | **Fails** — customers must modify production DB config |
| Performance | **Fails** — `general_log` logs all connections, significant I/O overhead |

Alternatives considered and rejected:

- **Performance Schema / `pg_stat_statements`** — history buffer overflows on busy DBs; not 100% complete
- **Per-user audit plugins** — DBMS-specific, requires plugin installation, not universal
- **Database proxy** — satisfies all requirements; adopted

---

## Solution: Per-Session Database Proxy

### Core principle

ArgusPAM runs a lightweight database proxy. JIT users connect their SQL client to the proxy instead of directly to the target database. The proxy speaks the native database wire protocol, authenticates the user with JIT credentials, opens its own connection to the real DB using those same credentials, and logs every query it intercepts.

**Admin credentials are never used by the proxy.** Admin is only ever called for JIT account creation and revocation, as today.

### What changes for end users

Before:
```
SQL Client → db.company.com:3306
```

After:
```
SQL Client → arguspam.company.com:15001   (proxy port assigned to this session)
             same JIT username and password
```

Connection strings, credentials format, and SQL clients are unchanged. Only the host and port differ.

---

## Architecture

### Full data flow

```
┌─────────────────────────────────────────────────────────────────┐
│                        SESSION APPROVAL                          │
└─────────────────────────────────────────────────────────────────┘
         ↓
[Laravel] Create JIT account on DB via admin credentials
[Laravel] POST /proxy → Go Proxy Service
         ↓
[Go Proxy Service]
  - Allocate port from pool (e.g. 15000–16000)
  - Spawn session proxy process on that port
  - Return { port: 15001 }
         ↓
[Laravel] Store proxy_port on session record
[Laravel] Return session to frontend with connection details

┌─────────────────────────────────────────────────────────────────┐
│                        SESSION ACTIVE                            │
└─────────────────────────────────────────────────────────────────┘

User's SQL Client → arguspam.company.com:15001
         ↓
[Go proxy — MySQL wire protocol]
  1. Complete MySQL/PG handshake with client (using JIT credentials to authenticate)
  2. Open own connection to real DB using same JIT credentials
  3. Intercept COM_QUERY / COM_STMT_EXECUTE packets
  4. Log: timestamp, query text, execution duration, affected rows, error (if any)
  5. Forward query to DB, forward response to client (transparent)
  6. Accumulate logs in memory buffer
  7. Flush batch → POST /internal/proxy/sessions/{id}/logs (every 5s or every 50 queries)
         ↓
[Laravel] Store incoming logs as SessionAudit records incrementally

┌─────────────────────────────────────────────────────────────────┐
│                        SESSION END                               │
│              (ended / expired / terminated / cancelled)          │
└─────────────────────────────────────────────────────────────────┘
         ↓
[Laravel] DELETE /proxy/{session_id} → Go Proxy Service
         ↓
[Go Proxy Service]
  - Send final log flush to Laravel
  - Drop active client connection
  - Free port back to pool
         ↓
[Laravel] Revoke JIT account on DB via admin credentials
[Laravel] Mark session_audits as complete
[Laravel] Fire SessionAiAudited → OpenAI analysis runs on full query set
```

### Security boundaries

```
Admin credentials:  JIT create ──────────────────────────── JIT revoke
                        ↑                                       ↑
                  (session start)                         (session end)

JIT credentials:              ── proxy auth ── proxy→DB ──
                                   ↑                  ↑
                             (user connects)    (proxy connects to DB)
```

Admin credentials never enter the proxy. The proxy's blast radius is bounded by what the JIT account can do.

---

## Components

### 1. Go Proxy Service (new)

A standalone Go service running as a Docker container. Exposes:
- An **internal HTTP management API** (called by Laravel, not exposed publicly)
- A **port range** bound on the host for incoming SQL client connections

```
proxy-service/
├── main.go
├── api/
│   ├── server.go          ← HTTP management API (start/stop/status)
│   └── handlers.go
├── pool/
│   └── ports.go           ← port pool allocation and release
├── proxy/
│   ├── session.go         ← per-session proxy lifecycle manager
│   ├── mysql/
│   │   ├── proxy.go       ← MySQL wire protocol proxy
│   │   └── parser.go      ← COM_QUERY / COM_STMT packet extraction
│   └── pgsql/
│       ├── proxy.go       ← PostgreSQL wire protocol proxy (Phase 2)
│       └── parser.go
└── shipper/
    └── shipper.go         ← batches query logs, flushes to Laravel API
```

**Go libraries:**
- `github.com/go-mysql-org/go-mysql` — MySQL wire protocol (server + client, has proxy utilities)
- `github.com/jackc/pgproto3/v2` — PostgreSQL wire protocol frontend/backend (Phase 2)

#### Management API

All endpoints are internal only (Docker network). Not exposed publicly.

```
POST   /proxy
Body:  {
         "session_id": "uuid",
         "dbms": "mysql",
         "jit_username": "pam_abc123",
         "jit_password": "...",
         "db_host": "db.company.com",
         "db_port": 3306
       }
Response: { "port": 15001 }

DELETE /proxy/{session_id}
Response: { "flushed": true, "total_queries": 142 }

GET    /proxy/{session_id}
Response: {
  "active": true,
  "port": 15001,
  "query_count": 87,
  "client_connected": true,
  "connected_at": "2026-01-01T10:00:00Z"
}

GET    /health
Response: 200 OK { "status": "ok", "active_sessions": 3 }
```

#### Per-session proxy behaviour

- Listens on allocated port for exactly one client connection at a time (PAM sessions are single-user)
- If client disconnects and reconnects within the active session window, the proxy accepts the new connection (same port, same session)
- Logs every statement with: `timestamp`, `query`, `duration_ms`, `rows_affected`, `error` (nullable)
- Buffers up to 50 queries or 5 seconds before flushing, whichever comes first
- On `DELETE /proxy/{id}`: flushes remaining buffer, closes listener, returns port to pool

#### Port pool

- Configurable range via env (`PROXY_PORT_RANGE_START`, `PROXY_PORT_RANGE_END`)
- Allocated ports tracked in-memory (map of `port → session_id`)
- Port returned to pool on session stop
- If pool is exhausted: return HTTP 503 to Laravel (Laravel surfaces this as session start failure)

### 2. Laravel Changes

#### Database migration

```php
// sessions table
$table->unsignedSmallInteger('proxy_port')->nullable()->after('asset_account_id');
```

#### New environment variables

```dotenv
# Internal URL to the Go proxy service (Docker network)
PROXY_SERVICE_URL=http://proxy-service:8080

# Public host users connect to (set to your domain, IP, or VPN hostname)
PROXY_PUBLIC_HOST=arguspam.company.com

# Shared secret — proxy uses this when posting logs to Laravel
PROXY_INTERNAL_SECRET=<random-256-bit-hex>

# Port range (must match proxy service config)
PROXY_PORT_RANGE_START=15000
PROXY_PORT_RANGE_END=16000
```

#### New config entry (`config/pam.php`)

```php
'proxy' => [
    'service_url'    => env('PROXY_SERVICE_URL', 'http://proxy-service:8080'),
    'public_host'    => env('PROXY_PUBLIC_HOST', 'localhost'),
    'internal_secret'=> env('PROXY_INTERNAL_SECRET'),
    'port_range'     => [
        'start' => env('PROXY_PORT_RANGE_START', 15000),
        'end'   => env('PROXY_PORT_RANGE_END', 16000),
    ],
],
```

#### New service: `ProxyService`

```php
// app/Services/Proxy/ProxyService.php

class ProxyService
{
    public function startSession(Session $session): int  // returns proxy port
    public function stopSession(Session $session): bool
    public function getSessionStatus(Session $session): array
}
```

Internally makes HTTP calls to the Go proxy management API. Handles connection failure gracefully (log + throw, session start fails cleanly).

#### Updated listeners

| Listener | Change |
|---|---|
| `HandleSessionStarted` | After JIT account creation: call `ProxyService::startSession()`, store `proxy_port` on session |
| `HandleSessionEnded` | Call `ProxyService::stopSession()` before revoking JIT account |
| `HandleSessionTerminated` | Same as ended |
| `HandleSessionExpired` | Same as ended |
| `HandleSessionCancelled` | If proxy was started (session had reached started state), call stop |

#### New internal route (log ingestion)

Separate route group, not part of the public API, protected by shared secret middleware:

```php
// routes/internal.php
Route::middleware('proxy.secret')->group(function () {
    Route::post('/proxy/sessions/{session}/logs', [ProxyLogController::class, 'store']);
});
```

```php
// app/Http/Middleware/VerifyProxySecret.php
// Checks X-Proxy-Secret header matches config('pam.proxy.internal_secret')
```

```php
// app/Http/Controllers/ProxyLogController.php
// Accepts batch of query log entries, stores as SessionAudit records
// Body: { "queries": [ { "timestamp": "...", "query": "...", "duration_ms": 12, ... } ] }
```

Registered in `bootstrap/app.php` under a separate route file (not `api.php`).

#### Updated Session API resource

When `status = started`, include in response:
```json
{
  "proxy_host": "arguspam.company.com",
  "proxy_port": 15001,
  "jit_username": "pam_abc123",
  "jit_password": "••••••••"   // exposed only on session resource, not in lists
}
```

`jit_password` is already stored encrypted on `AssetAccount`. Expose via the session resource only when the session is active and the requester is the session owner.

### 3. Docker Compose

```yaml
# docker-compose.yml addition

proxy-service:
  build:
    context: ./proxy-service
    dockerfile: Dockerfile
  restart: unless-stopped
  ports:
    - "${PROXY_PORT_RANGE_START:-15000}-${PROXY_PORT_RANGE_END:-16000}:${PROXY_PORT_RANGE_START:-15000}-${PROXY_PORT_RANGE_END:-16000}"
  environment:
    ARGUSPAM_API_URL: http://api
    ARGUSPAM_INTERNAL_SECRET: ${PROXY_INTERNAL_SECRET}
    PORT_RANGE_START: ${PROXY_PORT_RANGE_START:-15000}
    PORT_RANGE_END: ${PROXY_PORT_RANGE_END:-16000}
    FLUSH_INTERVAL_SECONDS: 5
    FLUSH_BATCH_SIZE: 50
  networks:
    - internal
  depends_on:
    - api
  healthcheck:
    test: ["CMD", "curl", "-f", "http://localhost:8080/health"]
    interval: 30s
    timeout: 5s
    retries: 3
```

The proxy service is on the internal Docker network only. The port range is the only thing exposed to the host (for SQL client connections). Port 8080 (management API) is not mapped to the host.

### 4. Frontend Changes

#### Session detail page — connection card

When `session.status === 'started'`, show a **"How to connect"** card:

```
┌─────────────────────────────────────────────────────┐
│  Connect to this session                             │
│                                                     │
│  Host      arguspam.company.com          [copy]     │
│  Port      15001                         [copy]     │
│  Username  pam_abc123                    [copy]     │
│  Password  ••••••••••  [reveal]          [copy]     │
│                                                     │
│  Connection string                                  │
│  mysql -h arguspam.company.com -P 15001 \           │
│    -u pam_abc123 -p                      [copy]     │
└─────────────────────────────────────────────────────┘
```

#### Session detail page — live query feed

During an active session, poll `GET /sessions/{id}/audit-logs` every 5 seconds and display an updating table of logged queries:

```
┌────────────────┬──────────────────────────────┬──────┐
│ Time           │ Query                         │  ms  │
├────────────────┼──────────────────────────────┼──────┤
│ 10:42:03       │ SELECT * FROM users LIMIT 10  │   4  │
│ 10:42:11       │ SELECT id, email FROM orders  │  12  │
│ 10:42:19       │ UPDATE users SET …            │   8  │
└────────────────┴──────────────────────────────┴──────┘
```

This gives approvers real-time visibility into what the JIT user is doing without waiting for session end.

---

## Build Phases

### Phase 1 — MySQL proxy + full integration

**Goal:** End-to-end working flow for MySQL assets. Removes `general_log` dependency entirely for MySQL.

| # | Task | Component |
|---|---|---|
| 1 | Go proxy service scaffold — management HTTP API, port pool | Go |
| 2 | MySQL wire protocol proxy (go-mysql) — handshake, query interception, forwarding | Go |
| 3 | Query log buffer + flush shipper (HTTP POST to Laravel) | Go |
| 4 | Dockerfile for proxy service | Go / Docker |
| 5 | Docker Compose update — proxy service, port range exposure | Docker |
| 6 | Laravel migration — `proxy_port` on sessions | Laravel |
| 7 | `ProxyService` — start/stop/status HTTP client | Laravel |
| 8 | `VerifyProxySecret` middleware + internal route group | Laravel |
| 9 | `ProxyLogController` — ingest query batches → SessionAudit | Laravel |
| 10 | Update `HandleSessionStarted` / `HandleSessionEnded` / expired / terminated | Laravel |
| 11 | Update Session API resource — expose proxy connection details | Laravel |
| 12 | Remove `retrieveUserQueryLogs()` from MySQL driver (no longer needed) | Laravel |
| 13 | Frontend: connection card on session detail page | SvelteKit |
| 14 | Frontend: live query feed (polling) on active session | SvelteKit |
| 15 | Tests — ProxyService, ProxyLogController, listener integration | Laravel |
| 16 | Update env examples and deployment docs | Docs |

### Phase 2 — PostgreSQL proxy

**Goal:** Same capability for PostgreSQL assets.

| # | Task | Component |
|---|---|---|
| 1 | PostgreSQL wire protocol proxy (pgproto3) — auth, simple/extended query interception | Go |
| 2 | Register PG driver in proxy service factory | Go |
| 3 | Remove `retrieveUserQueryLogs()` from PostgreSQL driver | Laravel |
| 4 | Tests | Go + Laravel |

### Phase 3 — Live monitoring enhancements (optional / future)

- Real-time risk alerts during session (flag high-risk queries as they happen)
- WebSocket or SSE instead of polling for live query feed
- Session admin controls: terminate directly from the live query view

---

## Security Considerations

### Proxy internal API
- Bound only on Docker internal network — not reachable from outside
- Additionally protected by `X-Proxy-Secret` shared secret header
- Two-layer defence: network isolation + secret

### JIT credential exposure
- Proxy host, port, username, and password are only returned in the session resource when:
  - `session.status === 'started'`
  - Requester is the authenticated session owner (policy check)
- Password is never logged, never included in audit logs, and masked in UI by default

### Proxy blast radius
- Proxy connects to DB using JIT credentials — limited to what that JIT account can do
- No admin credential stored or used in proxy process
- If proxy is compromised: attacker can only act within the JIT account's permissions (read-only or scoped databases as configured)

### Port exposure
- Only the port range is exposed on the host — not the management API
- Port range should be firewall-restricted to known user networks where possible (deployment concern, documented)

### Log ingestion endpoint
- `/internal/proxy/...` routes registered separately from public API
- `VerifyProxySecret` middleware blocks any request without valid secret
- Even if URL is discovered, no valid secret = 403

---

## Deployment Notes

### `PROXY_PUBLIC_HOST` configuration

This is the hostname or IP that end users put in their SQL client. It must be reachable from wherever JIT users are connecting from.

| Deployment model | Set `PROXY_PUBLIC_HOST` to |
|---|---|
| Public cloud | ArgusPAM server's public IP or domain |
| Internal / on-prem | Internal hostname or VPN IP of the ArgusPAM server |
| Split — internal users, public server | Internal hostname (if users are on VPN) |

### Port range sizing

Default: 15000–16000 = 1000 concurrent sessions. Adjust `PROXY_PORT_RANGE_START` / `PROXY_PORT_RANGE_END` based on expected concurrent session volume.

### Firewall recommendations (document for operators)

```
Allow inbound TCP 15000-16000 from: [user network CIDR]
Allow inbound TCP 443/80 from: anywhere (ArgusPAM web UI)
Deny inbound TCP 8080 from: anywhere (proxy management API — internal only)
```

---

## What is NOT changing

- JIT account creation and revocation logic — unchanged
- Admin credential handling — unchanged
- OpenAI audit analysis — unchanged (just runs on a richer, complete query set)
- `SessionAudit` model and storage — unchanged (logs written incrementally now vs. bulk at end)
- Approval workflow — unchanged
- All existing DBMS driver logic except `retrieveUserQueryLogs()` which is removed

---

## API Codebase Context

> Derived from exploring `api/` — kept here so future sessions have full context without re-reading the codebase.

### Session model (`app/Models/Session.php`)

Status values (enum `SessionStatus`): `SCHEDULED → STARTED → ENDED | TERMINATED | EXPIRED | CANCELLED`

Key fields relevant to proxy:
- `asset_account_id` — FK to the JIT `AssetAccount` created for this session
- `proxy_port` — **to be added** via migration (nullable unsignedSmallInteger)
- `started_at`, `ended_at`, `terminated_at`, `expired_at`

Key methods: `start()`, `end()`, `terminate()`, `cancel()`, `expire()`, `getAiAudit()`

### AssetAccount model (`app/Models/AssetAccount.php`)

- `type` enum: `ADMIN` | `JIT`
- `username`, `password` — Laravel encrypted casts (never logged, hidden from API)
- `databases` — JSON array of scoped database names
- `expires_at`, `ended_at`, `is_active`

JIT account credentials (username + password) are the same credentials the proxy uses to authenticate with the real DB.

### Event / listener flow

```
Session::start()
  → creates JIT AssetAccount via JitManager::createAccount()
  → dispatches SessionStarted event
  → dispatches SessionJitCreated event
  → HandleSessionStarted listener: sends notifications only

Session::end() / terminate()
  → dispatches SessionEnded / SessionTerminated
  → HandleSessionEndedOrTerminated listener:
      calls terminateJitAccount() (JitManager::terminateAccount)
      → calls driver->retrieveUserQueryLogs(username)   ← THIS IS REMOVED in Phase 1
      → SessionAudit::storeForSession(session, queryLogs)
      → dispatches SessionJitTerminated
  → HandleSessionJitTerminated listener:
      calls session->getAiAudit()
      → dispatches SessionAiAudited
```

**Proxy integration points (Phase 1 Laravel changes — not yet done):**
- After JIT account creation in `HandleSessionStarted`: call `ProxyService::startSession()`, store `proxy_port`
- Before JIT revocation in `HandleSessionEndedOrTerminated`: call `ProxyService::stopSession()` (triggers final flush)
- Remove `retrieveUserQueryLogs()` call — audits arrive incrementally via proxy log ingestion

### SessionAudit model (`app/Models/SessionAudit.php`)

Current fields: `org_id`, `session_id`, `asset_id`, `user_id`, `username`, `query`, `command_type`, `count`, `first_timestamp`, `last_timestamp`

Current storage: `SessionAudit::storeForSession(Session, Query[])` — bulk insert from aggregated general_log results.

**Schema note for Phase 1 Laravel work:** The proxy sends individual query events (timestamp, query, duration_ms, rows_affected, error). The `ProxyLogController` will need to map these to SessionAudit records. Fields `count`, `first_timestamp`, `last_timestamp` will need review — likely `count=1`, `first_timestamp=last_timestamp=query timestamp`. `duration_ms` and `rows_affected` may need new columns or can be encoded into `command_type`.

### Routes (`routes/api.php` + `bootstrap/app.php`)

- `apiPrefix: ''` — no `/api` prefix; routes are registered directly
- Route files registered in `bootstrap/app.php` via `withRouting(api: ..., commands: ...)`
- To add internal routes: register a new route file in `bootstrap/app.php` (e.g., `then:` callback loading `routes/internal.php`)
- Current middleware: `auth:sanctum`, `EnsureOrganizationIdIsValid`, `WantsJson` (prepended to all API)

### Session API Resource (`app/Http/Resources/Session/SessionResource.php`)

Currently returns all model fields. `proxy_host`, `proxy_port`, `jit_username`, `jit_password` not yet included — to be added in Phase 1 Laravel work, gated on `status === STARTED` and requester ownership.

### MySQL DBMS driver (`app/Services/Jit/Databases/Drivers/MySQLDriver.php`)

`retrieveUserQueryLogs(string $username): array` — queries `mysql.general_log`, returns `Query[]` objects.
This method is **removed in Phase 1** once the proxy ships.

---

## Open Questions / Future Considerations

- **MSSQL proxy**: TDS protocol. More complex than MySQL/PG. Defer until MySQL + PG are solid.
- **Multiple simultaneous connections per session**: Current design assumes one client connection per session (PAM model). If multi-connection sessions are ever needed, the proxy needs to accept multiple connections and fan-in logs.
- **Proxy-enforced policy**: The proxy already sees every query — future option to block DDL when request scope was read-only, without relying on DB-level permissions alone.
- **Session recording**: Beyond query text — record full result sets for forensic replay. Storage cost consideration.
