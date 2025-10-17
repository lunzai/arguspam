<?php

namespace App\Rules;

use App\Models\Org;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation rule to ensure a user group exists in a specific organization.
 *
 * This rule validates that the given user group ID belongs to the specified organization.
 */
class UserGroupExistedInOrg implements ValidationRule
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
        $existed = Org::find($this->orgId)
            ->userGroups()
            ->where('user_groups.id', $value)
            ->exists();
        if (!$existed) {
            $fail('The user group does not belong to this organization.');
        }
    }
}
