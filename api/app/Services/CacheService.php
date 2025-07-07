<?php

namespace App\Services;

use App\Enums\CacheKey;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function clearOrgCache(int $orgId): void
    {
        Cache::forget(CacheKey::USER_ORG->key($orgId));
        Cache::forget(CacheKey::ORG_USERS_COUNT->key($orgId));
        Cache::forget(CacheKey::ORG_USER_GROUPS_COUNT->key($orgId));
        Cache::forget(CacheKey::ORG_ASSETS_COUNT->key($orgId));
        Cache::forget(CacheKey::ORG_REQUESTS_COUNT->key($orgId));
        Cache::forget(CacheKey::ORG_REQUESTS_PENDING_COUNT->key($orgId));
        Cache::forget(CacheKey::ORG_SESSIONS_COUNT->key($orgId));
        Cache::forget(CacheKey::ORG_SESSIONS_SCHEDULED_COUNT->key($orgId));
        Cache::forget(CacheKey::ORG_SESSIONS_ACTIVE_COUNT->key($orgId));
    }

    public function clearUserOrgCache(int $userId): void
    {
        Cache::forget(CacheKey::USER_ORG->key($userId));
    }

    public function clearAllOrgCaches(): void
    {
        // Implementation for clearing all organization caches
        // This could use cache tags if your cache driver supports them
    }
}
