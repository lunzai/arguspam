<?php

namespace Tests\Unit\Services;

use App\Enums\CacheKey;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheServiceTest extends TestCase
{
    private CacheService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CacheService;
    }

    public function test_clear_org_cache_forgets_all_org_keys(): void
    {
        Cache::spy();

        $this->service->clearOrgCache(1);

        Cache::shouldHaveReceived('forget')->times(9);
    }

    public function test_clear_user_org_cache_forgets_user_key(): void
    {
        Cache::spy();

        $this->service->clearUserOrgCache(42);

        Cache::shouldHaveReceived('forget')
            ->once()
            ->with(CacheKey::USER_ORG->key(42));
    }

    public function test_clear_all_org_caches_runs_without_error(): void
    {
        $this->service->clearAllOrgCaches();

        $this->assertTrue(true);
    }
}
