# iris — Manual Test Cases

**Purpose:** End-to-end and integration manual tests. Covers happy paths, all edge cases identified across code review rounds 1–7, and concurrency/shutdown scenarios.

**Priority key:**

| Label | Meaning |
|---|---|
| P0 | Blocking — service is broken without this |
| P1 | High — core reliability; must pass before any release |
| P2 | Medium — edge cases; must pass for 99.99% uptime |
| P3 | Low — stress/boundary; run before major releases |

---

## Prerequisites

### Environment

```bash
# 1. A real MySQL 8.x instance reachable from your machine
MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
MYSQL_ROOT_PASS=root

# 2. A JIT account on that MySQL instance
mysql -h $MYSQL_HOST -P $MYSQL_PORT -u root -p$MYSQL_ROOT_PASS \
  -e "CREATE USER 'pam_test'@'%' IDENTIFIED BY 'testpass123'; GRANT SELECT,INSERT,UPDATE,DELETE ON testdb.* TO 'pam_test'@'%'; CREATE DATABASE IF NOT EXISTS testdb;"

# 3. iris built and running
cd iris/
go build -o iris .
ARGUSPAM_INTERNAL_SECRET=test-secret-999 \
ARGUSPAM_API_URL=http://localhost:9999 \
PORT_RANGE_START=15000 \
PORT_RANGE_END=15009 \
FLUSH_INTERVAL_SECONDS=5 \
FLUSH_BATCH_SIZE=50 \
  ./iris

# 4. A stub Laravel log ingestion endpoint (for shipper tests)
# Simple netcat server that accepts and prints POSTs:
nc -lk 9999    # or use the Python stub below

# Python stub (save as stub.py, run: python3 stub.py):
# from http.server import HTTPServer, BaseHTTPRequestHandler
# class H(BaseHTTPRequestHandler):
#     def do_POST(self):
#         n = int(self.headers.get('Content-Length', 0))
#         body = self.rfile.read(n)
#         print(f"[STUB] {self.path}\n{body.decode()}\n")
#         self.send_response(200); self.end_headers()
#     def log_message(self, *a): pass
# HTTPServer(('', 9999), H).serve_forever()

# 5. Shared constants used throughout
SECRET="test-secret-999"
BASE="http://localhost:8080"
```

### Notation

- Commands prefixed `→` are what you type/run.
- Lines prefixed `✓` are pass criteria.
- Lines prefixed `✗` describe failure conditions to watch for.
- `$SESSION` refers to a unique UUID generated per test run: `uuidgen` or `cat /proc/sys/kernel/random/uuid`.

---

## P0 — Critical

### TC-01 · Basic session start, connect, query, stop

**What it validates:** Core end-to-end proxy flow. If this fails nothing else matters.

**Steps:**

1. Generate a session ID.
   ```bash
   SESSION=$(uuidgen)
   ```

2. Start a proxy session.
   ```bash
   → curl -s -X POST $BASE/proxy \
       -H "X-Proxy-Secret: $SECRET" \
       -H "Content-Type: application/json" \
       -d "{\"session_id\":\"$SESSION\",\"dbms\":\"mysql\",\"jit_username\":\"pam_test\",\"jit_password\":\"testpass123\",\"db_host\":\"127.0.0.1\",\"db_port\":3306}"
   ```

3. Note the port returned. Use it to connect with the MySQL client.
   ```bash
   PORT=<port from response>
   → mysql -h 127.0.0.1 -P $PORT -u pam_test -ptestpass123 testdb
   ```

4. Run a query inside the MySQL session.
   ```sql
   → SELECT 1;
   → SELECT DATABASE();
   ```

5. Exit the MySQL client (`\q`).

6. Stop the session.
   ```bash
   → curl -s -X DELETE $BASE/proxy/$SESSION \
       -H "X-Proxy-Secret: $SECRET"
   ```

7. Check the stub Laravel server output.

**Expected results:**

- Step 2: `{"port": 15000}` (or another port in range). Status 201.
- Step 3: MySQL client connects and shows standard MySQL prompt. No auth error.
- Step 4: Queries execute and return results as normal.
- Step 6: `{"flushed":true,"total_queries":2}` (one per query). Status 200.
- Step 7: The stub received a POST to `/internal/proxy/sessions/$SESSION/logs` containing `SELECT 1` and `SELECT DATABASE()` in the `queries` array.

**✗ Fail if:** Port not returned; MySQL client refuses connection; queries time out; stop returns 0 queries; stub received no POST.

---

### TC-02 · Query log fields are complete and correctly typed

**What it validates:** Every field of `QueryLog` is populated. Duration is non-negative. Timestamp is UTC. `rows_affected` reflects actual DB result.

**Steps:**

1. Start a session (as TC-01 steps 1–2).
2. Connect and run:
   ```sql
   → INSERT INTO testdb.items (name) VALUES ('audit-test');
   → SELECT * FROM testdb.items;
   ```
3. Stop the session.
4. Inspect the stub payload.

**Expected results:**

- `timestamp` is a valid RFC-3339 UTC timestamp (ends in `Z`).
- `query` matches the exact SQL text sent.
- `duration_ms` is a non-negative integer.
- For the INSERT: `rows_affected` = 1.
- For the SELECT: `rows_affected` = row count returned.
- `error` field is absent or empty string on success.

**✗ Fail if:** Any field is missing, null, or negative. Timestamp is not UTC. `rows_affected` is always 0.

---

### TC-03 · Failed query is logged with its error

**What it validates:** Errors are captured in the audit log. The proxy forwards the error to the client and still logs the entry.

**Steps:**

1. Start a session.
2. Connect and run a query that will fail:
   ```sql
   → SELECT * FROM testdb.nonexistent_table;
   ```
3. Observe the MySQL client output.
4. Stop the session and inspect the stub payload.

**Expected results:**

- MySQL client receives the standard MySQL error response (table doesn't exist). Session does not drop.
- The stub payload contains one entry for the failed query.
- `error` field contains the MySQL error text (e.g., `"Table 'testdb.nonexistent_table' doesn't exist"`).
- `rows_affected` = 0.
- `duration_ms` is populated.

**✗ Fail if:** Proxy drops the connection on upstream error. Error is not recorded in the log.

---

### TC-04 · Missing authentication secret returns 401

**What it validates:** All management endpoints are protected. The proxy is not operable without the secret.

**Steps:**

1. Call each management endpoint without the `X-Proxy-Secret` header:
   ```bash
   → curl -s -o /dev/null -w "%{http_code}" -X POST $BASE/proxy \
       -H "Content-Type: application/json" \
       -d '{"session_id":"x","dbms":"mysql","jit_username":"u","jit_password":"p","db_host":"h","db_port":3306}'

   → curl -s -o /dev/null -w "%{http_code}" -X DELETE $BASE/proxy/any-id

   → curl -s -o /dev/null -w "%{http_code}" $BASE/proxy/any-id
   ```

**Expected results:**

- All three return HTTP `401`.
- Response body is `{"error":"unauthorized"}`.

**✗ Fail if:** Any endpoint responds 200 or 404 without authentication.

---

### TC-05 · Wrong authentication secret returns 401

**What it validates:** Timing-safe comparison rejects incorrect secrets without leaking timing information.

**Steps:**

1. Send a POST /proxy with a wrong but plausibly-similar secret:
   ```bash
   → curl -s -o /dev/null -w "%{http_code}" -X POST $BASE/proxy \
       -H "X-Proxy-Secret: wrong-secret-888" \
       -H "Content-Type: application/json" \
       -d '{"session_id":"x","dbms":"mysql","jit_username":"u","jit_password":"p","db_host":"h","db_port":3306}'
   ```

2. Send again with a one-character-off secret (timing check — not measurable manually but worth noting).

**Expected results:**

- HTTP `401` in all cases.
- Response body: `{"error":"unauthorized"}`.

**✗ Fail if:** 200 returned for any wrong secret value.

---

### TC-06 · Health check is public and returns ok

**What it validates:** `/health` works without authentication (Docker health check must not require a secret).

**Steps:**

```bash
→ curl -s $BASE/health
```

**Expected results:**

- HTTP `200`.
- Body: `{"status":"ok"}`.
- Response does **not** contain `active_sessions` (would expose operational metrics on a potentially public endpoint — verified in code review round 6).

**✗ Fail if:** Returns non-200. Returns 401. Contains `active_sessions` field.

---

## P1 — High

### TC-07 · Port pool exhaustion returns 503

**What it validates:** When all ports are in use, Laravel receives a 503 (not a 500 or a hang) and can surface this as a user-facing error.

**Setup:** Restart iris with a tiny pool: `PORT_RANGE_START=15000 PORT_RANGE_END=15001` (2 ports only).

**Steps:**

1. Start session A on port 15000.
2. Start session B on port 15001.
3. Attempt to start session C:
   ```bash
   → curl -s -X POST $BASE/proxy \
       -H "X-Proxy-Secret: $SECRET" \
       -H "Content-Type: application/json" \
       -d '{"session_id":"sess-C","dbms":"mysql","jit_username":"u","jit_password":"p","db_host":"127.0.0.1","db_port":3306}'
   ```

4. Stop session A.
5. Attempt to start session D (pool should now have one free port again).

**Expected results:**

- Step 3: HTTP `503`, body `{"error":"port pool exhausted"}`.
- Step 5: HTTP `201`, port returned successfully. Pool recovery works.

**✗ Fail if:** Step 3 returns 500 or hangs. Step 5 returns 503 after a port was released.

---

### TC-08 · Duplicate session ID is rejected

**What it validates:** Starting the same session twice does not overwrite the existing session or allocate a second port.

**Steps:**

1. Start session `sess-dup`.
2. Immediately start `sess-dup` again with the same session ID:
   ```bash
   → curl -s -X POST $BASE/proxy \
       -H "X-Proxy-Secret: $SECRET" \
       -H "Content-Type: application/json" \
       -d '{"session_id":"sess-dup","dbms":"mysql","jit_username":"u","jit_password":"p","db_host":"127.0.0.1","db_port":3306}'
   ```
3. Check how many ports are now allocated (compare `PORT_RANGE_END - PORT_RANGE_START + 1 - active` to pool size).
4. Stop `sess-dup`.

**Expected results:**

- Step 2: Error response (any non-201), body contains `"already exists"` or similar.
- Step 3: Exactly one port allocated (not two). No port leak.
- Step 4: Returns `{"flushed":true,"total_queries":...}` successfully.

**✗ Fail if:** Second start returns 201. Two ports are consumed by one session.

---

### TC-09 · Stop a non-existent session returns 404

**What it validates:** Idempotent stop behaviour. Laravel can safely call stop even if iris has already forgotten the session (e.g. after a restart).

**Steps:**

```bash
→ curl -s -X DELETE $BASE/proxy/does-not-exist \
    -H "X-Proxy-Secret: $SECRET"
```

**Expected results:**

- HTTP `404`.
- Body: `{"error":"session not found"}`.

**✗ Fail if:** Returns 200, 500, or panics.

---

### TC-10 · Client disconnect and reconnect within the same session window

**What it validates:** The accept loop continues accepting new connections after a client disconnects. The session does not tear itself down on client exit. (Critical for users who restart their SQL client mid-session.)

**Steps:**

1. Start a session.
2. Connect with MySQL client, run `SELECT 1`, then exit the client (`\q`).
3. Wait 2 seconds.
4. Reconnect with a new MySQL client invocation to the same port.
5. Run `SELECT 2`.
6. Exit and stop the session.

**Expected results:**

- Step 4: Second connection is accepted without error. No "connection refused".
- Step 6: `total_queries` = 2 (one from each connection).
- Stub receives both queries in the flush.

**✗ Fail if:** Step 4 returns "connection refused". Session has torn itself down after client disconnect.

---

### TC-11 · Status endpoint reflects connected/disconnected state

**What it validates:** `client_connected` toggles correctly as clients connect and disconnect.

**Steps:**

1. Start a session.
2. Check status — no client yet:
   ```bash
   → curl -s $BASE/proxy/$SESSION -H "X-Proxy-Secret: $SECRET"
   ```
3. Connect a MySQL client (keep it open).
4. Check status again.
5. Exit the MySQL client.
6. Wait 1 second, check status again.

**Expected results:**

- Step 2: `"client_connected": false`.
- Step 4: `"client_connected": true`, `"active": true`, `"port"` matches allocated port.
- Step 6: `"client_connected": false` (connection has dropped).
- `session_started_at` is a valid UTC timestamp in all responses.

**✗ Fail if:** `client_connected` is always `false` or always `true`.

---

### TC-12 · All queries are flushed on stop (no query loss)

**What it validates:** The shutdown flush (`drainAndSendFinal`) delivers everything buffered at stop time, even if the batch threshold hasn't been reached.

**Steps:**

1. Restart iris with `FLUSH_BATCH_SIZE=100` and `FLUSH_INTERVAL_SECONDS=60` (so nothing flushes automatically).
2. Start a session.
3. Connect and run exactly 7 queries:
   ```sql
   SELECT 1; SELECT 2; SELECT 3; SELECT 4; SELECT 5; SELECT 6; SELECT 7;
   ```
4. Exit the MySQL client.
5. Stop the session immediately (before any interval flush fires).

**Expected results:**

- `total_queries` in the stop response = 7.
- The stub received exactly 7 query entries across all its batches.
- No queries are missing.

**✗ Fail if:** `total_queries` < 7. Stub received fewer than 7 queries. Some queries silently dropped.

---

### TC-13 · SIGTERM causes graceful shutdown with final flush

**What it validates:** The service drains in-flight HTTP requests and flushes all active session buffers before exiting. No queries are lost on container restart.

**Steps:**

1. Start iris.
2. Start a session and connect a MySQL client.
3. Run 3 queries.
4. While the MySQL client is still connected, send SIGTERM to the iris process:
   ```bash
   → kill -SIGTERM $(pgrep iris)
   ```
5. Observe iris log output and stub output.

**Expected results:**

- iris logs: `"shutting down..."` then `"shutdown complete"`.
- The MySQL client connection is dropped (iris closed the listener).
- Stub received all 3 queries in its final flush POST before the process exited.
- iris exits with code 0 (or at most 1 — not segfault).

**✗ Fail if:** iris hangs indefinitely. Queries are lost. iris exits before flushing.

---

## P2 — Medium (Edge Cases from Code Reviews)

### TC-14 · Unsupported DBMS returns 400

**Source:** Code review round 5, issue H-1 — DBMS validation added.

**What it validates:** Attempting to proxy an unsupported database type is rejected before any port is allocated.

**Steps:**

```bash
→ curl -s -X POST $BASE/proxy \
    -H "X-Proxy-Secret: $SECRET" \
    -H "Content-Type: application/json" \
    -d '{"session_id":"sess-pg","dbms":"postgres","jit_username":"u","jit_password":"p","db_host":"h","db_port":5432}'

→ curl -s -X POST $BASE/proxy \
    -H "X-Proxy-Secret: $SECRET" \
    -H "Content-Type: application/json" \
    -d '{"session_id":"sess-ms","dbms":"mssql","jit_username":"u","jit_password":"p","db_host":"h","db_port":1433}'
```

**Expected results:**

- Both return HTTP `400`.
- Body: `{"error":"unsupported dbms: only mysql is supported"}`.
- No port is consumed (pool size unchanged).

**✗ Fail if:** Returns 201. Port is allocated for an unsupported DBMS. Returns 500.

---

### TC-15 · Invalid db_port values are rejected

**Source:** Code review round 5 — port validation tightened to `1–65535`.

**Steps:**

Test each invalid port value:
```bash
# db_port = 0
→ curl -s -X POST $BASE/proxy -H "X-Proxy-Secret: $SECRET" \
    -H "Content-Type: application/json" \
    -d '{"session_id":"s1","dbms":"mysql","jit_username":"u","jit_password":"p","db_host":"h","db_port":0}'

# db_port = 65536
→ curl -s -X POST $BASE/proxy -H "X-Proxy-Secret: $SECRET" \
    -H "Content-Type: application/json" \
    -d '{"session_id":"s2","dbms":"mysql","jit_username":"u","jit_password":"p","db_host":"h","db_port":65536}'

# db_port = -1
→ curl -s -X POST $BASE/proxy -H "X-Proxy-Secret: $SECRET" \
    -H "Content-Type: application/json" \
    -d '{"session_id":"s3","dbms":"mysql","jit_username":"u","jit_password":"p","db_host":"h","db_port":-1}'
```

**Expected results:**

- All three return HTTP `400`, body `{"error":"missing required fields"}`.

**✗ Fail if:** Any returns 201. Port 0 silently becomes valid. Port 65536 is accepted.

---

### TC-16 · Prepared statement arguments are logged (not just the template)

**Source:** Code review round 5/6, issue M-3 — `HandleStmtExecute` appends args to the logged query.

**What it validates:** When a client uses prepared statements, the audit log captures the resolved values, not just `?` placeholders.

**Steps:**

1. Start a session.
2. Connect with MySQL client and run a prepared statement:
   ```sql
   → PREPARE stmt FROM 'SELECT * FROM testdb.items WHERE id = ?';
   → SET @id = 42;
   → EXECUTE stmt USING @id;
   → DEALLOCATE PREPARE stmt;
   ```
3. Stop the session and inspect the stub payload.

**Expected results:**

- The stub payload contains an entry for the SELECT execution.
- The `query` field includes the bound value, e.g.:
  `"SELECT * FROM testdb.items WHERE id = ? -- args: [42]"`
- The template `?` alone is **not** sufficient — the args suffix must be present.

**✗ Fail if:** Query is logged as `SELECT * FROM testdb.items WHERE id = ?` with no args. Args are empty even though a value was bound.

---

### TC-17 · Oversized prepared statement arguments are truncated at rune boundaries

**Source:** Code review round 7, issue M-3 — truncation must be UTF-8/rune-safe (not byte-slice).

**What it validates:** A very long argument value is truncated to 256 runes with a `…` suffix. Critically, the truncation must not split a multi-byte UTF-8 character mid-sequence.

**Steps:**

1. Start a session.
2. Connect and execute a prepared statement with a long multi-byte argument. Construct a 300-character Japanese string in your SQL client (each char is 3 bytes in UTF-8):
   ```sql
   -- In the mysql CLI, paste a 300-char string of "あ" (U+3042, 3 bytes each)
   → PREPARE stmt FROM 'INSERT INTO testdb.items (name) VALUES (?)';
   → SET @long = REPEAT('あ', 300);
   → EXECUTE stmt USING @long;
   ```
3. Stop the session and inspect the stub payload for the INSERT entry.

**Expected results:**

- The `query` field ends with `…` (ellipsis character).
- The text before `…` contains exactly 256 `あ` characters (256 runes, not 256 bytes).
- The JSON in the stub payload is valid (no broken UTF-8 sequences that would cause JSON parse failure).

**✗ Fail if:** Truncation splits a multi-byte character (JSON parse fails or arg shows garbled characters). Truncation happens at 256 bytes instead of 256 runes. No truncation at all (full 300-char string present).

---

### TC-18 · Concurrent Stop called during Start — no port leak

**Source:** Code review rounds 3–5, issues H-2/H-3 — TOCTOU race on concurrent Start+Stop; slot struct sentinel pattern.

**What it validates:** If Stop is called before Start finishes (e.g. a rapid cancel from Laravel), the port is always returned to the pool.

**Steps:**

1. Use a tiny pool: `PORT_RANGE_START=15000 PORT_RANGE_END=15000` (1 port only).
2. Open two terminal windows.
3. In terminal A, start a session with an unreachable DB host (dial will block for up to 15s):
   ```bash
   → curl -s -X POST $BASE/proxy \
       -H "X-Proxy-Secret: $SECRET" \
       -H "Content-Type: application/json" \
       -d '{"session_id":"race-sess","dbms":"mysql","jit_username":"u","jit_password":"p","db_host":"192.0.2.1","db_port":3306}' &
   ```
4. Immediately (within 1 second) in terminal B, stop the same session:
   ```bash
   → curl -s -X DELETE $BASE/proxy/race-sess \
       -H "X-Proxy-Secret: $SECRET"
   ```
5. Wait for terminal A's curl to return.
6. Attempt to start a new session on the same pool:
   ```bash
   → curl -s -X POST $BASE/proxy \
       -H "X-Proxy-Secret: $SECRET" \
       -H "Content-Type: application/json" \
       -d '{"session_id":"new-sess","dbms":"mysql","jit_username":"u","jit_password":"p","db_host":"127.0.0.1","db_port":3306}'
   ```

**Expected results:**

- Step 4: Returns `{"flushed":true,"total_queries":0}` or `404` (depending on timing — either is valid).
- Step 5: Terminal A returns an error (session cancelled or dial failed) — not a 201.
- Step 6: Returns `201` with a port. **The port is not stuck.** This is the key assertion: no port leak.

**✗ Fail if:** Step 6 returns 503 (pool exhausted). Port 15000 is locked forever.

---

### TC-19 · Stop during upstream dial is cancelled immediately

**Source:** Code review round 7, issue H-2 — `s.stopped` channel wired to dial context.

**What it validates:** Calling `Stop()` while the upstream TCP dial is in progress (blocked on an unreachable host) unblocks within milliseconds, not 15 seconds.

**Steps:**

1. Start iris normally.
2. Start a session to an unreachable host (RFC-5737 documentation IP — guaranteed unreachable):
   ```bash
   time curl -s -X POST $BASE/proxy \
       -H "X-Proxy-Secret: $SECRET" \
       -H "Content-Type: application/json" \
       -d '{"session_id":"dial-test","dbms":"mysql","jit_username":"u","jit_password":"p","db_host":"192.0.2.1","db_port":3306}' &
   ```
3. Wait 1 second (dial is now in progress — 15s timeout).
4. Stop the session and measure how long it takes:
   ```bash
   time curl -s -X DELETE $BASE/proxy/dial-test \
       -H "X-Proxy-Secret: $SECRET"
   ```

**Expected results:**

- The stop curl returns in well under 15 seconds (target: < 2 seconds).
- iris logs: `[session dial-test] upstream dial failed` (or similar).
- No goroutine leak — the dial goroutine exits, not blocked until the 15s timeout expires.

**✗ Fail if:** Stop takes 15 seconds (the full `dialTimeout`). iris logs show the dial completed after the stop returned.

---

### TC-20 · Client hard-disconnect does not log an error in iris

**Source:** Code review round 5, issue H-5 — `isDisconnectErr` covers `ECONNRESET`, `EPIPE`, go-mysql string fallbacks.

**What it validates:** Normal client disconnects (RST, FIN, closed socket) are treated as clean exits, not logged as errors. Only genuine command errors produce log noise.

**Steps:**

1. Start a session.
2. Connect a MySQL client.
3. Hard-kill the MySQL client process (not a clean `\q` — send SIGKILL or close the terminal without `\q`):
   ```bash
   # In another terminal, after connecting:
   → kill -9 $(pgrep mysql)
   ```
4. Observe the iris process log output.

**Expected results:**

- iris logs do **not** contain `command error:` or similar for this disconnect.
- iris does **not** log `ECONNRESET` or `broken pipe` as an error.
- The session remains active (accept loop continues; a new connection is accepted).
- iris only logs `[session ...] client connected` on the next connection.

**✗ Fail if:** iris logs `command error: ... connection reset by peer` (noisy; correct behaviour logs nothing for clean disconnect). Session tears down on hard disconnect.

---

### TC-21 · Session stop is idempotent (double-stop)

**Source:** Code review round — `stop()` uses `sync.Once`.

**What it validates:** Calling stop twice (e.g. race between Laravel retry and a timeout) does not panic, double-release a port, or corrupt the pool.

**Steps:**

1. Start a session.
2. Send two DELETE requests for the same session in rapid succession (background both):
   ```bash
   curl -s -X DELETE $BASE/proxy/$SESSION -H "X-Proxy-Secret: $SECRET" &
   curl -s -X DELETE $BASE/proxy/$SESSION -H "X-Proxy-Secret: $SECRET" &
   wait
   ```
3. Check pool health by starting a new session immediately after.

**Expected results:**

- One DELETE returns 200, the other returns 404 (only one succeeds — the second sees the slot already deleted).
- iris does not panic or log any fatal errors.
- A new session can be started on the now-freed port.

**✗ Fail if:** iris panics. Both return 200. Port is counted as freed twice (pool count wrong). New session can't start.

---

### TC-22 · Shipper retries on transient Laravel API errors

**Source:** Code review rounds — `sendWithRetry` with 3 attempts and exponential back-off.

**What it validates:** Transient 500s from Laravel do not cause permanent query loss. The shipper retries up to 3 times.

**Steps:**

1. Start a stub server that returns 500 for the first 2 POSTs, then 200 on the 3rd.
   (Modify the Python stub to count requests and fail the first two.)

2. Start a session and add 2 queries.
3. Stop the session (triggers a final flush attempt).
4. Observe iris logs and stub output.

**Expected results:**

- iris logs show retry attempts: `send attempt 1/3 failed` and `send attempt 2/3 failed`.
- On the 3rd attempt, the stub receives the queries and returns 200.
- iris logs no permanent failure.
- All queries appear in the stub's final successful request.

**✗ Fail if:** iris gives up after 1 failure and logs `PERMANENT FLUSH FAILURE`. Queries are lost even though the API recovered.

---

### TC-23 · Shipper permanent failure is loudly logged

**What it validates:** If all 3 retry attempts fail, iris logs the count of lost queries. This is the last-resort signal to operators.

**Steps:**

1. Start a stub server that always returns 500.
2. Start a session, add 3 queries, stop the session.
3. Observe iris logs.

**Expected results:**

- iris logs: `PERMANENT FLUSH FAILURE after 3 attempts: ... — 3 queries lost`.
- iris does not crash or hang.
- `total_queries` in the stop response still reflects 3 (the counter is in-memory, separate from delivery).

**✗ Fail if:** iris crashes. No log entry for lost queries. Stop hangs indefinitely.

---

### TC-24 · Shutdown does not block on shipper retry sleep

**Source:** Code review rounds — retry back-off sleep is interruptible via `s.done` channel.

**What it validates:** If the shipper is mid-retry sleep (waiting 500ms between attempts) when SIGTERM arrives, the service exits promptly rather than waiting out the full sleep.

**Steps:**

1. Start a stub server that always returns 500 (forces retries).
2. Start a session. Add enough queries to trigger a batch flush (≥ `FLUSH_BATCH_SIZE`).
3. While the shipper is retrying (observe the `send attempt` log lines), send SIGTERM to iris:
   ```bash
   → kill -SIGTERM $(pgrep iris)
   ```
4. Measure time from SIGTERM to process exit.

**Expected results:**

- iris exits within ~5 seconds of SIGTERM (not after the full 30s shutdown timeout or a retry sleep).
- Logs show: `shutting down...` → one final send attempt → `shutdown complete`.

**✗ Fail if:** iris hangs for the full retry wait (500ms + 1000ms + ...) before responding to SIGTERM. Service takes > 30s to exit.

---

### TC-25 · Port boundary values — first and last port in range

**What it validates:** The pool correctly handles the exact start and end port numbers.

**Steps:**

1. Restart iris with `PORT_RANGE_START=15000 PORT_RANGE_END=15001`.
2. Confirm pool has exactly 2 ports.
3. Start session A — note port (should be 15001 — pool pops from the end).
4. Start session B — note port (should be 15000).
5. Attempt session C — expect 503.
6. Stop session A.
7. Start session C — expect 201 with port 15001 returned.

**Expected results:**

- Sessions A and B each get distinct ports from `{15000, 15001}`.
- Session C (step 5) gets 503.
- Session C (step 7) gets 201 — port is recycled correctly.

**✗ Fail if:** Any port outside `15000–15001` is returned. Port is not returned to pool on stop.

---

### TC-26 · `USE DATABASE` is forwarded to upstream

**What it validates:** `HandleUseDB` proxies database-switching correctly. Without this, `USE mydb` would silently fail or apply only on the proxy side.

**Steps:**

1. Start a session.
2. Connect a MySQL client without specifying a database:
   ```bash
   → mysql -h 127.0.0.1 -P $PORT -u pam_test -ptestpass123
   ```
3. Run `USE testdb;` then `SELECT DATABASE();`

**Expected results:**

- `SELECT DATABASE()` returns `testdb`.
- No error from the `USE` command.

**✗ Fail if:** `USE` returns an error. `SELECT DATABASE()` returns NULL.

---

## P3 — Stress / Boundary

### TC-27 · Many concurrent sessions start simultaneously

**What it validates:** No race conditions in Manager.Start under concurrent load. No port double-allocation.

**Setup:** Restart iris with `PORT_RANGE_START=15000 PORT_RANGE_END=15049` (50 ports).

**Steps:**

1. Start 50 sessions simultaneously using background jobs:
   ```bash
   for i in $(seq 1 50); do
     curl -s -X POST $BASE/proxy \
       -H "X-Proxy-Secret: $SECRET" \
       -H "Content-Type: application/json" \
       -d "{\"session_id\":\"sess-$i\",\"dbms\":\"mysql\",\"jit_username\":\"pam_test\",\"jit_password\":\"testpass123\",\"db_host\":\"127.0.0.1\",\"db_port\":3306}" &
   done
   wait
   ```
2. Collect all returned ports and check for duplicates:
   ```bash
   # Pipe the above through jq or grep to collect ports and check uniqueness
   ```
3. Start a 51st session — expect 503.

**Expected results:**

- All 50 responses are 201 with unique ports.
- No duplicate ports assigned.
- 51st session returns 503.
- No iris panic or fatal log.

**✗ Fail if:** Any two sessions share the same port. 503 returned before pool is actually full. iris panics.

---

### TC-28 · Stop all active sessions on SIGTERM flushes concurrently

**What it validates:** `StopAll` runs session stops in parallel — total shutdown time is bounded by the slowest single session, not the sum of all sessions.

**Steps:**

1. Start 10 sessions, connect a MySQL client to each, run 5 queries per session (50 total queries).
2. Send SIGTERM.
3. Measure total time from SIGTERM to `shutdown complete` log.

**Expected results:**

- Total shutdown time < 15 seconds (not 10 × 5s).
- Stub receives all 50 queries (across 10 batches).
- iris exits cleanly.

**✗ Fail if:** Shutdown takes > 30s. Queries are lost across some sessions. iris deadlocks.

---

### TC-29 · StopAll with expired shutdown context — partial flush is logged

**What it validates:** If `StopAll` exceeds the 30-second context deadline, iris logs the warning and exits rather than hanging indefinitely.

**Setup:** This requires artificially making sessions take a long time to stop (e.g. a stub that responds very slowly).

**Steps:**

1. Start a stub that sleeps 40 seconds per POST (simulates a very slow Laravel API).
2. Start 3 sessions, add queries to each.
3. Send SIGTERM.
4. Observe log output and exit behaviour.

**Expected results:**

- After ~30 seconds, iris logs: `[manager] StopAll deadline exceeded; some sessions may not have flushed cleanly`.
- iris exits (does not hang past the 30s deadline + some OS buffer).
- Log clearly indicates which data may be lost.

**✗ Fail if:** iris hangs indefinitely past 30 seconds. No warning is logged about the deadline being exceeded.

---

### TC-30 · Large request body is rejected (4 KB limit)

**What it validates:** `http.MaxBytesReader` protects against oversized POST bodies on `POST /proxy`.

**Steps:**

```bash
# Generate a body over 4 KB (pad the session_id with a 5 KB string)
→ LONG=$(python3 -c "print('x' * 5000)")
→ curl -s -X POST $BASE/proxy \
    -H "X-Proxy-Secret: $SECRET" \
    -H "Content-Type: application/json" \
    -d "{\"session_id\":\"$LONG\",\"dbms\":\"mysql\",\"jit_username\":\"u\",\"jit_password\":\"p\",\"db_host\":\"h\",\"db_port\":3306}"
```

**Expected results:**

- HTTP `400` (request body too large or invalid JSON from truncation).
- No port allocated.
- iris does not OOM or hang reading an infinite body.

**✗ Fail if:** 201 returned with a 5 KB session ID. iris hangs or crashes.

---

## Test Execution Summary Template

Use this table to record results during a test run:

| TC | Priority | Description | Result | Notes |
|---|---|---|---|---|
| TC-01 | P0 | Basic start / connect / query / stop | | |
| TC-02 | P0 | Query log fields complete | | |
| TC-03 | P0 | Failed query logged with error | | |
| TC-04 | P0 | Missing secret → 401 | | |
| TC-05 | P0 | Wrong secret → 401 | | |
| TC-06 | P0 | Health check public | | |
| TC-07 | P1 | Pool exhaustion → 503 | | |
| TC-08 | P1 | Duplicate session ID rejected | | |
| TC-09 | P1 | Stop non-existent → 404 | | |
| TC-10 | P1 | Client reconnect within session | | |
| TC-11 | P1 | Status reflects connected state | | |
| TC-12 | P1 | No query loss on stop | | |
| TC-13 | P1 | SIGTERM graceful shutdown | | |
| TC-14 | P2 | Unsupported DBMS → 400 | | |
| TC-15 | P2 | Invalid db_port rejected | | |
| TC-16 | P2 | Prepared stmt args logged | | |
| TC-17 | P2 | Long arg truncated at rune boundary | | |
| TC-18 | P2 | Concurrent Stop during Start — no port leak | | |
| TC-19 | P2 | Stop cancels in-progress dial | | |
| TC-20 | P2 | Hard disconnect not logged as error | | |
| TC-21 | P2 | Double-stop is idempotent | | |
| TC-22 | P2 | Shipper retries on transient 500 | | |
| TC-23 | P2 | Permanent failure loudly logged | | |
| TC-24 | P2 | Shutdown not blocked by retry sleep | | |
| TC-25 | P2 | Port boundary values (first/last) | | |
| TC-26 | P2 | USE DATABASE forwarded | | |
| TC-27 | P3 | 50 concurrent session starts | | |
| TC-28 | P3 | StopAll concurrent — bounded shutdown time | | |
| TC-29 | P3 | StopAll deadline exceeded — warning logged | | |
| TC-30 | P3 | Large request body rejected | | |

**All P0 and P1 must pass. All P2 must pass for 99.99% uptime target. P3 before major releases.**
