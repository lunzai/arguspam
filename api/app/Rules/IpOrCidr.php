<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IpOrCidr implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check for null or non-string values
        if ($value === null || !is_string($value)) {
            $fail('The :attribute must be a valid IP address or CIDR notation.');
            return;
        }

        // Check if it's a valid IP address
        if (filter_var($value, FILTER_VALIDATE_IP)) {
            return;
        }

        // Check if it's a valid CIDR notation
        if (preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}\/([0-9]|[1-2][0-9]|3[0-2])$/', $value)) {
            [$ip, $netmask] = explode('/', $value);

            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return;
            }
        }

        $fail('The :attribute must be a valid IP address or CIDR notation.');
    }
}
