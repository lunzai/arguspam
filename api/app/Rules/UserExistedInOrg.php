<?php

namespace App\Rules;

use App\Models\Org;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserExistedInOrg implements ValidationRule
{
    protected $orgId;

    public function __construct($orgId)
    {
        $this->orgId = $orgId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $existed = Org::find($this->orgId)
            ->users()
            ->where('users.id', $value)
            ->exists();
        if (!$existed) {
            $fail('The user does not belong to this organization.');
        }
    }
}
