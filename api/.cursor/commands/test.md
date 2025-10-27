# Continue Unit Testing

**Goal**: Achieve 80%+ test coverage for the Laravel API project.

## Context
- Current Coverage: **45.20%** (2347/5193 lines)
- Target Coverage: **80%+**
- Test Suite: PHPUnit 12.4.1 with Laravel
- Total Tests: 1698 tests with 26 failures/errors

## Your Tasks

### 1. Check Current Status
First, identify where we are in the testing roadmap:
- Read the plan file: `80--test-coverage-plan.plan.md`
- Check which todos are completed/in-progress
- Run tests to see current failures: `XDEBUG_MODE=coverage vendor/bin/phpunit`

### 2. Priority Order
Work through these phases in order:

**Phase 1: Fix Existing Test Failures (26 total)**
- SessionPolicy tests (5 errors): Missing `deleteAny()`, `restoreAny()` methods
- SessionAuditPolicyTest: Empty test class
- Events/AllEventsTest (4 errors): SessionAiReviewed vs SessionAiAudited mismatch
- Listeners/AllListenersTest (1 failure): Event name inconsistency
- Notification tests (16 failures): Missing introduction text
- User model test (1 failure): Missing includable relationships

**Phase 2: Add Listener Tests (23.78% coverage)**
- Test the 13 untested listener classes in `app/Listeners/`
- Follow patterns in existing listener tests

**Phase 3: Add Notification Tests (10.34% class coverage)**
- Test 26 untested notification classes
- Focus on instantiation, toMail(), toArray(), via() methods

**Phase 4: Enhance Model Tests**
- Add comprehensive Session model tests (canStart, canEnd, canCancel, canTerminate)
- Test edge cases and state transitions

**Phase 5: Add Controller Tests (4.16% coverage)**
Create tests following the pattern in `tests/Unit/Controllers/AuthControllerSimpleTest.php`:
- `SessionRequesterControllerTest.php` for `app/Http/Controllers/SessionRequesterController.php`
- `SessionApproverControllerTest.php` for `app/Http/Controllers/SessionApproverController.php`
- Other untested controllers (33 controllers total, only 1 has tests)

### 3. Testing Patterns to Follow

**Controller Tests:**
```php
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Session, User};

class SessionRequesterControllerTest extends TestCase
{
    use RefreshDatabase;
    
    // Test controller instantiation
    // Test each method (show, store, update, delete)
    // Test authorization checks
    // Test with factories for models
}
```

**Model Tests:**
```php
// Test model methods
// Test relationships
// Test scopes
// Test attributes/casts
// Test business logic methods
```

**Policy Tests:**
```php
// Test each policy method
// Test with different user permissions
// Test authorization rules
```

### 4. Commands You'll Need
- Run all tests: `vendor/bin/phpunit`
- Run with coverage: `XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text`
- Run specific test: `vendor/bin/phpunit tests/Unit/Controllers/SessionRequesterControllerTest.php`
- Generate HTML coverage: `XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html coverage-report`

### 5. Key Files to Reference
- Test case base: `tests/TestCase.php`
- Existing controller test: `tests/Unit/Controllers/AuthControllerSimpleTest.php`
- Model factories: `database/factories/`
- Coverage report: `coverage-report/index.html`

### 6. Success Criteria
- All existing tests pass (0 failures/errors)
- Code coverage reaches 80%+ 
- New tests follow existing patterns
- Tests are focused and maintainable

## Instructions
1. Start by checking the plan and current test results
2. Pick the next uncompleted todo from the plan
3. Work on it systematically
4. Run tests frequently to verify progress
5. Update the plan todos as you complete them
6. When done with a phase, move to the next one
7. Keep me informed of progress and any blockers

**Remember**: Follow Laravel testing best practices, use factories for test data, and ensure tests are isolated and repeatable.

