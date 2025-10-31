# Continue Unit Testing

**Goal**: Follow the Unit Test Strategy & Plan to reach P0 100% and P1 90% coverage with fast, pure unit tests.

## Context
- Test Runner: PHPUnit 12.x with Laravel
- Plan Source: `/.cursor/unit-test-plan.md`

## Your Tasks

### 1) Check Current Status
- Read `/.cursor/unit-test-plan.md` (Migration Plan + Current Status).
- Run the Unit suite to see current failures: `vendor/bin/phpunit --testsuite=Unit`.
- If measuring, run with coverage: `vendor/bin/phpunit --testsuite=Unit --coverage-text`.

### 2) Work Through Migration Phases (from the plan)

**Phase 1: Organize Tests (DONE)**
1. Create `tests/Integration/` directory ✅
2. Move database-dependent tests from `tests/Unit/` to `tests/Integration/` ✅
3. Update `phpunit.xml` test suites ✅

**Phase 2: Convert to Pure Unit Tests**
1. Rewrite controllers/services/policies in `tests/Unit/*` to use mocks only
2. Update `tests/Unit/Models/*` to test logic only (no DB)

**Phase 3: Achieve Coverage Goals**
1. Add missing unit tests for uncovered P0/P1 code
2. Measure coverage: `vendor/bin/phpunit --coverage-html coverage-report`

**Phase 4: Optimize**
1. Parallelize unit tests where applicable
2. Use in-memory SQLite for integration tests
3. Ensure CI runs fast unit tests gate

### 3) Testing Patterns to Follow
- See templates in `/.cursor/unit-test-plan.md`:
  - Controllers/Services use mocks (no DB, no HTTP, no files)
  - Policies mock `User::hasAnyPermission()`
  - Models: test logic, casts, accessors; move relationships/scopes to Integration

### 4) Commands You'll Need
- Run Unit tests only (fast):
  - `vendor/bin/phpunit --testsuite=Unit`
  - With coverage: `vendor/bin/phpunit --testsuite=Unit --coverage-text`
- Run Integration tests:
  - `vendor/bin/phpunit --testsuite=Integration`
- Run both Unit + Integration:
  - `vendor/bin/phpunit --testsuite=Unit,Integration`
- Run full suite:
  - `vendor/bin/phpunit`
- Generate HTML coverage report:
  - `XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html coverage-report`
- Run a specific test file:
  - `vendor/bin/phpunit tests/Unit/Path/To/YourTest.php`

### 5) Key Files to Reference
- Plan and templates: `/.cursor/unit-test-plan.md`
- Base test case: `tests/TestCase.php`
- Example controller test: `tests/Unit/Controllers/AuthControllerSimpleTest.php`
- Model factories: `database/factories/`
- Coverage HTML: `coverage-report/index.html`

### 6) Success Criteria
- 0 failures/errors in Unit suite
- P0 code at 100% coverage; P1 code at 90%+
- Unit tests are pure (no DB/HTTP/files) and fast (< 0.1s per test)
- Tests follow the provided templates and are maintainable

## Instructions
1. Phase 1: Organize tests (create Integration suite, move DB tests, update phpunit.xml).
2. Phase 2: Convert Unit tests to pure mocks per templates (no DB/HTTP/files).
3. Phase 3: Add tests to hit P0/P1 coverage targets; generate coverage report.
4. Phase 4: Optimize (parallelize, in-memory SQLite for Integration, CI gating).

Remember: If it touches the database, it's Integration — not Unit.