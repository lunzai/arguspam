# Unit Test Strategy & Plan

**Purpose:** Guide all future testing work to achieve 100% unit test coverage with fast, maintainable tests.

**Last Updated:** 2025-10-31

---

## 🎯 Core Testing Philosophy

### The Three Types of Tests

| Type | What | Tools | Speed | Location |
|------|------|-------|-------|----------|
| **Unit** | Test code logic in isolation | Mocks/Stubs | ⚡ < 0.1s | `tests/Unit/` |
| **Integration** | Test components working together | Real DB | 🐌 1-5s | `tests/Integration/` |
| **Feature** | End-to-end user flows | HTTP requests | 🐢 5-30s | `tests/Feature/` |

### Golden Rules

1. **Unit tests NEVER touch the database**
2. **Unit tests NEVER make HTTP requests**
3. **Unit tests NEVER read files**
4. **Unit tests mock ALL dependencies**
5. **Each unit test runs in < 0.1 seconds**

---

## 📁 Directory Structure

```
tests/
├── Unit/                          ← Pure unit tests (NO DATABASE)
│   ├── Console/
│   │   └── Commands/             ← Mock Artisan commands
│   ├── Controllers/              ← Mock services, return resources
│   ├── Enums/                    ← No mocks needed (pure logic)
│   ├── Events/                   ← Test event data
│   ├── Filters/                  ← Mock query builder
│   ├── Http/
│   │   └── Resources/            ← Test data transformation
│   ├── Jobs/                     ← Mock dependencies
│   ├── Listeners/                ← Mock events
│   ├── Models/                   ← Test casts, accessors, scopes (mock DB)
│   ├── Notifications/            ← Test notification data
│   ├── Policies/                 ← Mock User::hasAnyPermission()
│   ├── Requests/                 ← Test validation rules
│   ├── Rules/                    ← Test validation logic
│   ├── Services/                 ← Mock all dependencies
│   └── Traits/                   ← Test trait logic
│
├── Integration/                   ← Tests with database
│   ├── Models/                   ← Test relationships, scopes with DB
│   ├── Policies/                 ← Test RBAC system end-to-end
│   ├── Repositories/             ← Test complex queries
│   └── Services/                 ← Test services with real DB
│
└── Feature/                       ← End-to-end API tests
    ├── Auth/                     ← Login, logout flows
    ├── API/                      ← Full HTTP request/response
    └── Services/                 ← Complex workflows
```

---

## ✅ What to Unit Test (Priority Order)

### P0: Critical (Target: 100% coverage)

**Controllers**
- ✅ Methods call correct service methods
- ✅ Return correct resource types
- ✅ Handle authorization
- ❌ Don't test database queries

```php
// ✅ GOOD: Unit test
public function test_index_returns_user_collection()
{
    $users = collect([new User(['name' => 'Test'])]);

    $service = Mockery::mock(UserService::class);
    $service->shouldReceive('getAll')->once()->andReturn($users);

    $controller = new UserController($service);
    $result = $controller->index();

    $this->assertInstanceOf(UserCollection::class, $result);
}

// ❌ BAD: Integration test disguised as unit test
public function test_index_returns_users()
{
    User::factory()->count(3)->create(); // Database!

    $controller = new UserController;
    $result = $controller->index();

    $this->assertCount(3, $result);
}
```

**Services**
- ✅ Business logic calculations
- ✅ Data transformations
- ✅ Method calls to dependencies
- ❌ Don't test actual database operations

**Policies**
- ✅ Authorization logic flow
- ✅ Permission checks called correctly
- ❌ Don't test actual permission lookup

```php
// ✅ GOOD: Unit test
public function test_update_returns_true_when_user_has_permission()
{
    $user = Mockery::mock(User::class);
    $user->shouldReceive('hasAnyPermission')
        ->once()
        ->with('user:update')
        ->andReturn(true);

    $policy = new UserPolicy();
    $this->assertTrue($policy->update($user, new User()));
}

// ❌ BAD: Integration test
public function test_update_with_real_permissions()
{
    $user = User::factory()->create(); // Database!
    $this->giveUserPermission($user, 'user:update'); // Database!

    $this->assertTrue((new UserPolicy)->update($user, new User()));
}
```

### P1: Important (Target: 90% coverage)

**Commands**
- Mock command dependencies
- Test output messages
- Test return codes

**Jobs**
- Mock all external dependencies
- Test job logic
- Test failure handling

**Event Listeners**
- Mock events
- Test listener actions
- Test notification dispatch

### P2: Nice to Have (Target: 80% coverage)

**Resources**
- Test data transformation
- Test conditional fields
- Test relationships

**Requests**
- Test validation rules
- Test custom validation logic

**Filters**
- Mock query builder
- Test filter logic

### P3: Deprioritize

**Low Value Tests:**
- Simple getters/setters
- Framework code wrappers
- Third-party library wrappers
- Code scheduled for removal

**Too Complex to Mock:**
- Deep Eloquent magic
- Complex query builder chains
- _(Move these to Integration tests)_

---

## 🚫 What NOT to Unit Test

**Move to Integration Tests:**
1. Eloquent relationships (`hasMany`, `belongsTo`, etc.)
2. Database scopes and query builders
3. Model factories
4. Database migrations
5. RBAC system (roles → permissions → users)
6. Complex database transactions

**Move to Feature Tests:**
1. Full API request/response flows
2. Middleware chains
3. Authentication flows
4. Multi-step user workflows

**Don't Test At All:**
1. Framework code (trust Laravel)
2. Vendor package code (trust packages)
3. Simple data containers (DTOs with no logic)
4. Auto-generated code

---

## 📝 Unit Test Templates

### Controller Test Template

```php
<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\UserController;
use App\Services\UserService;
use App\Http\Resources\User\UserCollection;
use Mockery;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_calls_service_and_returns_collection(): void
    {
        // Arrange
        $users = collect([/* mock data */]);
        $service = Mockery::mock(UserService::class);
        $service->shouldReceive('getAll')->once()->andReturn($users);

        // Act
        $controller = new UserController($service);
        $result = $controller->index();

        // Assert
        $this->assertInstanceOf(UserCollection::class, $result);
    }
}
```

### Service Test Template

```php
<?php

namespace Tests\Unit\Services;

use App\Services\UserService;
use App\Repositories\UserRepository;
use Mockery;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_user_calls_repository(): void
    {
        // Arrange
        $data = ['name' => 'Test', 'email' => 'test@example.com'];
        $repository = Mockery::mock(UserRepository::class);
        $repository->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn(new User($data));

        // Act
        $service = new UserService($repository);
        $user = $service->createUser($data);

        // Assert
        $this->assertEquals('Test', $user->name);
    }
}
```

### Policy Test Template

```php
<?php

namespace Tests\Unit\Policies;

use App\Policies\UserPolicy;
use App\Models\User;
use Mockery;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_view_returns_true_when_user_has_permission(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasAnyPermission')
            ->once()
            ->with('user:view')
            ->andReturn(true);

        // Act
        $policy = new UserPolicy();
        $result = $policy->view($user);

        // Assert
        $this->assertTrue($result);
    }

    public function test_view_returns_false_when_user_lacks_permission(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasAnyPermission')
            ->once()
            ->with('user:view')
            ->andReturn(false);

        // Act
        $policy = new UserPolicy();
        $result = $policy->view($user);

        // Assert
        $this->assertFalse($result);
    }
}
```

**For AI Assistants / Future Prompts:**

When working on tests:
1. ALWAYS check this document first
2. ONLY write unit tests in `tests/Unit/` (use mocks)
3. Move DB tests to `tests/Integration/`
4. Use the templates provided
5. Prioritize P0 > P1 > P2 coverage
6. Deprioritize P3 items
7. Keep tests fast (< 0.1s per unit test)
8. Ask if unsure whether something should be unit or integration tested

**Focus:** Get to 100% coverage on P0 code with pure, fast unit tests.
