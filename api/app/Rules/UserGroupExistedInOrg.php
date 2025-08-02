<?php

namespace App\Rules;

use App\Models\Org;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserGroupExistedInOrg implements ValidationRule
{
    protected $orgId;

    public function __construct($orgId)
    {
        $this->orgId = $orgId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $existed = Org::find($this->orgId)
            ->userGroups()
            ->where('user_groups.id', $value)
            ->exists();
        if (!$existed) {
            $fail('The user group does not belong to this organization.');
        }
    }
}
