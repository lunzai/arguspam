# Testing Guide for Database Services

## Overview

This testing suite covers the refactored Database and Secrets services with comprehensive unit tests, integration tests, and feature tests.

## Test Structure

### Unit Tests (`tests/Unit/Services/`)
- **Fast execution** (mocked dependencies)
- **Isolated testing** of individual services
- **High coverage** of business logic

### Integration Tests (`tests/Feature/Services/`)
- **Real database connections** for MySQL/PostgreSQL
- **End-to-end JIT lifecycle** testing
- **Encryption/decryption** verification

### Feature Tests (`tests/Feature/Services/`)
- **Service orchestration** testing
- **Database access resolution** testing
- **Error handling** scenarios

## Running Tests

### 1. Unit Tests (Fast)
```bash
# Run all unit tests
php artisan test tests/Unit/Services/

# Run specific service tests
php artisan test tests/Unit/Services/CredentialManagerTest.php
php artisan test tests/Unit/Services/JitAccountManagerTest.php
php artisan test tests/Unit/Services/DatabaseDriverFactoryTest.php
```

### 2. Integration Tests (Requires Test Database)
```bash
# Set up test database first
export TEST_MYSQL_HOST=127.0.0.1
export TEST_MYSQL_DATABASE=arguspam_test
export TEST_MYSQL_USERNAME=root
export TEST_MYSQL_PASSWORD=your_password

# Run integration tests
php artisan test tests/Feature/Services/JitLifecycleIntegrationTest.php
```

### 3. All Tests
```bash
# Run everything
php artisan test

# With coverage
php artisan test --coverage
```

## Test Database Setup

### MySQL Test Database
```sql
CREATE DATABASE arguspam_test;
CREATE USER 'test_user'@'localhost' IDENTIFIED BY 'test_password';
GRANT ALL PRIVILEGES ON arguspam_test.* TO 'test_user'@'localhost';
FLUSH PRIVILEGES;
```

### Environment Variables
```bash
# .env.testing
TEST_MYSQL_HOST=127.0.0.1
TEST_MYSQL_DATABASE=arguspam_test
TEST_MYSQL_USERNAME=test_user
TEST_MYSQL_PASSWORD=test_password

TEST_PGSQL_HOST=127.0.0.1
TEST_PGSQL_DATABASE=arguspam_test
TEST_PGSQL_USERNAME=test_user
TEST_PGSQL_PASSWORD=test_password
```

## Test Scenarios Covered

### ✅ Unit Tests
- [x] CredentialManager - Admin credentials retrieval
- [x] CredentialManager - Error handling
- [x] JitAccountManager - Account creation
- [x] JitAccountManager - Account termination
- [x] JitAccountManager - Validation handling
- [x] DatabaseDriverFactory - MySQL driver creation
- [x] DatabaseDriverFactory - PostgreSQL driver creation
- [x] DatabaseDriverFactory - Database name resolution
- [x] DatabaseAccessResolverChain - Resolution priority
- [x] DatabaseAccessResolverChain - Fallback handling

### ✅ Integration Tests
- [x] Full JIT lifecycle (create → terminate)
- [x] All databases access (null databases)
- [x] Multiple databases access
- [x] Single database access
- [x] Encryption/decryption verification
- [x] MySQL integration
- [x] PostgreSQL integration

### ✅ Feature Tests
- [x] Admin credentials priority
- [x] Request databases priority
- [x] Asset databases priority
- [x] Missing admin account handling
- [x] Termination without JIT account
- [x] Error propagation

## Key Test Assertions

### Encryption/Decryption
```php
// Password should be encrypted in database
$this->assertStringStartsWith('eyJ', $jitAccount->password);

// But decrypted when accessed through model
$this->assertEquals($plainPassword, $jitAccount->password);
```

### Database Access Resolution
```php
// Priority: admin > request > asset > default
$result = $resolver->resolve($session, $adminCreds);
$this->assertEquals(['expected_db'], $result);
```

### JIT Account Lifecycle
```php
// Create
$result = $secretsManager->createAccount($session);
$this->assertTrue($result->success);

// Verify in database
$this->assertDatabaseHas('asset_accounts', [
    'type' => AssetAccountType::JIT,
    'is_active' => true,
]);

// Terminate
$result = $secretsManager->terminateAccount($session);
$this->assertTrue($result->success);

// Verify deletion
$this->assertDatabaseMissing('asset_accounts', [
    'id' => $jitAccount->id,
]);
```

## Continuous Integration

### GitHub Actions Example
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: arguspam_test
        ports:
          - 3306:3306
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: php artisan test
        env:
          TEST_MYSQL_HOST=127.0.0.1
          TEST_MYSQL_DATABASE=arguspam_test
          TEST_MYSQL_USERNAME=root
          TEST_MYSQL_PASSWORD=password
```

## Debugging Tests

### Enable Query Logging
```php
// In test setUp()
DB::enableQueryLog();

// In test
$queries = DB::getQueryLog();
$this->assertCount(2, $queries); // Expected query count
```

### Debug Service Dependencies
```php
// Check service resolution
$secretsManager = app(SecretsManager::class);
$this->assertInstanceOf(SecretsManager::class, $secretsManager);
```

### Mock External Dependencies
```php
// Mock database driver for unit tests
$mockDriver = $this->createMock(DatabaseDriverInterface::class);
$mockDriver->expects($this->once())
    ->method('createUser')
    ->willReturn(true);
```

## Performance Considerations

- **Unit tests**: ~50ms each (mocked)
- **Integration tests**: ~200ms each (real DB)
- **Full suite**: ~30 seconds

## Coverage Goals

- **Unit tests**: 95%+ coverage
- **Integration tests**: Critical paths only
- **Feature tests**: User workflows

## Troubleshooting

### Common Issues

1. **Test database not found**
   - Ensure test database exists
   - Check environment variables
   - Verify database permissions

2. **Encryption errors**
   - Check APP_KEY is set
   - Verify model casts are correct
   - Test with fresh database

3. **Mock failures**
   - Verify method signatures match
   - Check expectation counts
   - Use `$this->any()` for flexible mocking

### Debug Commands
```bash
# Run single test with verbose output
php artisan test tests/Unit/Services/CredentialManagerTest.php --verbose

# Run with debug output
php artisan test --debug

# Check test database connection
php artisan tinker
>>> DB::connection('testing_mysql')->getPdo();
```
