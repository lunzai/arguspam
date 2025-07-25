<?php

namespace App\Rules;

use App\Enums\CacheKey;
use App\Models\Org;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;

class UserExistedInOrg implements ValidationRule
{
    protected $orgId;

    public function __construct($orgId)
    {
        $this->orgId = $orgId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // $orgUsers = Cache::remember(
        //     CacheKey::ORG_USERS->key($this->orgId),
        //     config('cache.default_ttl'),
        //     function () {
        //         return Org::find($this->orgId)->users()->get();
        //     }
        // );
        $existed = Org::find($this->orgId)->users()->where('users.id', $value)->exists();

        if (!$existed) {
            $fail('The user does not belong to this organization.');
        }
    }
}
