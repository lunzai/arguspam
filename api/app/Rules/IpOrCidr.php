<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation rule for IP addresses and CIDR notation.
 *
 * This rule validates that the given value is either a valid IP address
 * (IPv4 or IPv6) or a valid CIDR notation (e.g., 192.168.1.0/24).
 */
class IpOrCidr implements ValidationRule
{
    /**
     * Validate the given attribute value.
     *
     * @param  string  $attribute  The attribute name being validated
     * @param  mixed  $value  The value being validated
     * @param  Closure  $fail  The failure callback
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
