<?php

namespace App\Rules;

use App\Enums\CacheKey;
use App\Models\Org;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;

/**
 * Validation rule to ensure a user exists in a specific organization.
 *
 * This rule validates that the given user ID belongs to the specified organization.
 * It uses caching to improve performance by storing organization users for 1 hour.
 */
class UserExistedInOrg implements ValidationRule
{
    protected $orgId;

    public function __construct($orgId)
    {
        $this->orgId = $orgId;
    }

    /**
     * Validate the given attribute value.
     *
     * @param  string  $attribute  The attribute name being validated
     * @param  mixed  $value  The value being validated
     * @param  Closure  $fail  The failure callback
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cacheKey = CacheKey::ORG_USERS->key($this->orgId);

        // Try to get cached users first
        $orgUsers = Cache::get($cacheKey);

        if ($orgUsers === null) {
            // Cache miss - fetch from database and cache
            $orgUsers = Org::find($this->orgId)->users()->get();
            Cache::put($cacheKey, $orgUsers, 3600); // Cache for 1 hour
        }

        $existed = $orgUsers->contains('id', $value);

        if (!$existed) {
            $fail('The user does not belong to this organization.');
        }
    }
}
