# Unit Test Coverage Improvement Plan

**Last Updated:** 2025-10-31  
**Goal:** Achieve 100% P0, 90% P1, 80% P2 coverage following unit-test-plan.md

---

## Current Status

### Coverage Summary
- **Enums:** ✅ 100% (Well covered)
- **Notifications:** ✅ 100% (Well covered)
- **Rules:** ⚠️ ~50% (Some tests exist)
- **Traits:** ⚠️ Mixed (ApiResponses: 100%, HasStatus: 89%, Others: 0-30%)
- **Controllers:** ❌ 0% (0/33 tested)
- **Services:** ⚠️ 25% (1/4 main services, 0 JIT services)
- **Policies:** ❌ 0% (0/10 tested)
- **Commands:** ❌ Unknown coverage
- **Jobs:** ❌ Unknown coverage
- **Resources:** ❌ Unknown coverage
- **Filters:** ⚠️ Some tests exist

---

## Priority-Based Action Plan

### 🔴 P0: Critical (Target: 100% coverage)

#### 1. Controllers (33 controllers, 0% coverage)
**Priority:** Highest - These are the entry points for all API requests

**High Priority Controllers (Start Here):**
1. ✅ `AuthController` - Authentication (login, me)
2. ✅ `UserController` - User CRUD operations
3. ✅ `RequestController` - Request management
4. ✅ `SessionController` - Session management
5. ✅ `AssetController` - Asset management
6. ✅ `RoleController` - Role management
7. ✅ `DashboardController` - Dashboard data

**Medium Priority Controllers:**
8. `OrgController` - Organization management
9. `UserGroupController` - User group management
10. `PermissionController` - Permission management
11. `AssetAccountController` - Asset account management
12. `AssetConnectionController` - Asset connection management
13. `AccessRestrictionController` - Access restriction management
14. `SessionAuditController` - Session audit viewing
15. `PasswordController` - Password management
16. `TwoFactorAuthenticationController` - 2FA management

**Lower Priority Controllers:**
17-33. Relationship controllers (UserOrgController, UserRoleController, etc.)

**Testing Approach:**
- Mock all service dependencies
- Mock authorization checks (`$this->authorize()`)
- Mock Eloquent queries (use `Model::shouldReceive('filter')->andReturn()`)
- Test return types are correct Resources
- Verify service methods are called with correct parameters
- **NEVER** test database queries or relationships

**Example Template:**
```php
public function test_index_returns_collection(): void
{
    $service = Mockery::mock(SomeService::class);
    $items = collect([new Item(['id' => 1])]);
    $service->shouldReceive('getAll')->once()->andReturn($items);
    
    $controller = new SomeController($service);
    $result = $controller->index();
    
    $this->assertInstanceOf(ItemCollection::class, $result);
}
```

#### 2. Services (4 main + JIT services, ~25% coverage)
**Priority:** High - Core business logic

**Services with Tests:**
- ✅ `CacheService` (100%)

**Services Needing Tests:**
1. ⚠️ `DashboardService` - Dashboard metrics (DB heavy - may need refactoring for pure unit tests)
2. ⚠️ `PolicyPermissionService` - Policy reflection (file reading - needs mocking)
3. ⚠️ `OpenAIService` - AI integration (needs mocking of OpenAI client)

**JIT Services (app/Services/Jit/):**
4. `JitManager` - JIT account management
5. `CredentialManager` - Credential handling
6. `SecretsManager` - Secrets management
7. `DatabaseDriverFactory` - Driver factory
8. `UserCreationValidator` - User validation

**Testing Approach:**
- Mock all external dependencies (OpenAI, DB, File system)
- Test business logic calculations
- Test error handling
- Test data transformations
- **NEVER** test actual database/file operations

#### 3. Policies (10 policies, 0% coverage)
**Priority:** High - Authorization is critical

**Policies to Test:**
1. `UserPolicy` - User authorization
2. `RequestPolicy` - Request authorization
3. `SessionPolicy` - Session authorization
4. `AssetPolicy` - Asset authorization
5. `RolePolicy` - Role authorization
6. `OrgPolicy` - Organization authorization
7. `PermissionPolicy` - Permission authorization
8. `UserGroupPolicy` - User group authorization
9. `AccessRestrictionPolicy` - Access restriction authorization
10. `ActionAuditPolicy` - Audit authorization

**Testing Approach:**
- Mock `User::hasAnyPermission()` calls
- Test permission checks are called correctly
- Test authorization logic flow
- **NEVER** test actual permission lookup from DB

**Example Template:**
```php
public function test_update_returns_true_when_user_has_permission(): void
{
    $user = Mockery::mock(User::class);
    $user->shouldReceive('hasAnyPermission')
        ->once()
        ->with('user:update')
        ->andReturn(true);
    
    $policy = new UserPolicy();
    $this->assertTrue($policy->update($user, new User()));
}
```

---

### 🟡 P1: Important (Target: 90% coverage)

#### 4. Commands (app/Console/Commands/)
**Priority:** Medium-High

**Commands to Test:**
- Check all commands in `app/Console/Commands/`
- Mock command dependencies
- Test output messages
- Test return codes
- Test command options and arguments

#### 5. Jobs (app/Jobs/)
**Priority:** Medium

**Jobs to Test:**
- `ProcessExpiredSessions` - Session expiration processing

**Testing Approach:**
- Mock all external dependencies
- Test job logic
- Test failure handling
- Test retry logic

#### 6. Event Listeners (app/Listeners/)
**Priority:** Medium

**Current Status:**
- ✅ Some listeners have tests (HandleRequestApproved, HandleSessionAiReviewed)
- ⚠️ Some listeners need more comprehensive tests

**Listeners to Review:**
- Ensure all listeners have tests
- Mock all dependencies (events, models, notifications)
- Test listener actions
- Test notification dispatch

---

### 🟢 P2: Nice to Have (Target: 80% coverage)

#### 7. Resources (app/Http/Resources/)
**Priority:** Low-Medium

**Testing Approach:**
- Test data transformation
- Test conditional fields
- Test relationships (mocked)
- Test array/json serialization

#### 8. Requests (app/Http/Requests/)
**Priority:** Medium

**Current Status:**
- ✅ Some request tests exist (LoginRequest, etc.)

**Testing Approach:**
- Test validation rules
- Test custom validation logic
- Test authorization checks (mocked)
- **NEVER** test database lookups (move to integration)

#### 9. Filters (app/Http/Filters/)
**Priority:** Low-Medium

**Current Status:**
- ✅ SessionFilter has tests

**Testing Approach:**
- Mock query builder
- Test filter logic
- Test filter combinations
- **NEVER** test actual DB queries

---

### 🔵 P3: Deprioritize

- Simple getters/setters
- Framework code wrappers
- Third-party library wrappers
- Code scheduled for removal

---

## Implementation Phases

### Phase 1: P0 - Controllers (Weeks 1-2)
**Goal:** 100% controller coverage

1. **Week 1: High Priority Controllers**
   - AuthController
   - UserController
   - RequestController
   - SessionController

2. **Week 2: Medium Priority Controllers**
   - AssetController
   - RoleController
   - DashboardController
   - OrgController

**Estimated:** 30-40 unit tests

### Phase 2: P0 - Services & Policies (Weeks 3-4)
**Goal:** 100% service and policy coverage

1. **Week 3: Services**
   - DashboardService (with refactoring if needed)
   - PolicyPermissionService
   - OpenAIService
   - JIT Services (JitManager, CredentialManager, etc.)

2. **Week 4: Policies**
   - UserPolicy
   - RequestPolicy
   - SessionPolicy
   - AssetPolicy
   - RolePolicy
   - Remaining policies

**Estimated:** 40-50 unit tests

### Phase 3: P1 - Commands, Jobs, Listeners (Week 5)
**Goal:** 90% coverage

1. Commands testing
2. Jobs testing
3. Complete listener coverage

**Estimated:** 20-30 unit tests

### Phase 4: P2 - Resources, Requests, Filters (Week 6)
**Goal:** 80% coverage

1. Resources testing
2. Complete request validation testing
3. Complete filter testing

**Estimated:** 20-30 unit tests

---

## Testing Best Practices (From unit-test-plan.md)

### ✅ DO:
- Mock ALL dependencies
- Test business logic and data transformations
- Test method calls with correct parameters
- Test return types
- Keep tests fast (< 0.1s each)
- Use templates from unit-test-plan.md

### ❌ DON'T:
- Touch the database
- Make HTTP requests
- Read files (mock file operations)
- Test framework code
- Test third-party library code
- Test simple getters/setters

---

## Quick Start Commands

```bash
# Run unit tests
./vendor/bin/phpunit --testsuite=Unit

# Run with coverage
composer unit-coverage

# Run specific test file
./vendor/bin/phpunit tests/Unit/Controllers/AuthControllerTest.php

# Check coverage for specific class
XDEBUG_MODE=coverage ./vendor/bin/phpunit --testsuite=Unit --coverage-text --coverage-filter=App\\Http\\Controllers\\AuthController
```

---

## Next Steps

1. **Start with AuthController** - It's simple and high-impact
2. **Follow the template** from unit-test-plan.md
3. **Create one test file at a time**
4. **Verify no database/file operations** - all mocks
5. **Run tests frequently** to ensure they stay fast
6. **Move DB-dependent code to Integration tests** if needed

---

## Notes

- Controllers with complex queries may need service layer extraction
- Services that read files need file operation mocking
- AI services need OpenAI client mocking
- Some services may be better suited for Integration tests if they're mostly DB operations
