<?php

namespace Tests\Unit\Services;

use App\Enums\CacheKey;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheServiceTest extends TestCase
{
    protected CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = new CacheService;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_clears_all_organization_cache_keys(): void
    {
        // Arrange
        $orgId = 123;

        // Expect all org-related cache keys to be cleared
        Cache::shouldReceive('forget')
            ->once()
            ->with(CacheKey::USER_ORG->key($orgId));

        Cache::shouldReceive('forget')
            ->once()
            ->with(CacheKey::ORG_USERS_COUNT->key($orgId));

        Cache::shouldReceive('forget')
            ->once()
            ->with(CacheKey::ORG_USER_GROUPS_COUNT->key($orgId));

        Cache::shouldReceive('forget')
            ->once()
            ->with(CacheKey::ORG_ASSETS_COUNT->key($orgId));

        Cache::shouldReceive('forget')
            ->once()
            ->with(CacheKey::ORG_REQUESTS_COUNT->key($orgId));

        Cache::shouldReceive('forget')
            ->once()
            ->with(CacheKey::ORG_REQUESTS_PENDING_COUNT->key($orgId));

        Cache::shouldReceive('forget')
            ->once()
            ->with(CacheKey::ORG_SESSIONS_COUNT->key($orgId));

        Cache::shouldReceive('forget')
            ->once()
            ->with(CacheKey::ORG_SESSIONS_SCHEDULED_COUNT->key($orgId));

        Cache::shouldReceive('forget')
            ->once()
            ->with(CacheKey::ORG_SESSIONS_ACTIVE_COUNT->key($orgId));

        // Act
        $this->cacheService->clearOrgCache($orgId);

        // Assert - expectations are verified by Mockery
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_clears_user_org_cache(): void
    {
        // Arrange
        $userId = 789;

        Cache::shouldReceive('forget')
            ->once()
            ->with(CacheKey::USER_ORG->key($userId));

        // Act
        $this->cacheService->clearUserOrgCache($userId);

        // Assert
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_clear_all_org_caches_without_errors(): void
    {
        // Act - currently a no-op
        $this->cacheService->clearAllOrgCaches();

        // Assert - method should complete without errors
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_verifies_all_cache_keys_are_correctly_formatted(): void
    {
        // Arrange
        $orgId = 100;
        $expectedKeys = [
            'user:org:100',
            'org:user:count:100',
            'org:user_group:count:100',
            'org:asset:count:100',
            'org:request:count:100',
            'org:request:pending:count:100',
            'org:session:count:100',
            'org:session:scheduled:count:100',
            'org:session:active:count:100',
        ];

        foreach ($expectedKeys as $key) {
            Cache::shouldReceive('forget')->once()->with($key);
        }

        // Act
        $this->cacheService->clearOrgCache($orgId);

        // Assert
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_be_instantiated(): void
    {
        // Arrange & Act
        $service = new CacheService;

        // Assert
        $this->assertInstanceOf(CacheService::class, $service);
    }

}
