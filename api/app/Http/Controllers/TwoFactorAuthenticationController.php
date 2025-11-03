<?php

namespace App\Http\Controllers;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Enable two-factor authentication
     */
    public function store(User $user)
    {
        $this->authorize('enrollTwoFactorAuthentication', $user);
        if ($user->two_factor_enabled) {
            return $this->unprocessableEntity('Two-factor authentication is already enabled.');
        }
        if ($user->two_factor_confirmed_at) {
            return $this->unprocessableEntity('Two-factor authentication is already enrolled.');
        }
        $user->two_factor_enabled = true;
        $user->two_factor_secret = $user->generateTwoFactorSecret();
        $user->two_factor_recovery_codes = [];
        $user->two_factor_confirmed_at = null;
        $user->two_factor_enabled_at = now();
        $user->save();

        return $this->created();
    }

    /**
     * Enrollment: Show two-factor authentication QR code
     */
    public function show(User $user)
    {
        $this->authorize('enrollTwoFactorAuthentication', $user);
        if (!$user->two_factor_enabled) {
            return $this->unprocessableEntity('Two-factor authentication is not enabled.');
        }
        if ($user->two_factor_confirmed_at) {
            return $this->unprocessableEntity('Two-factor authentication is already enrolled.');
        }
        return $this->success([
            'qr_code' => $user->twoFactorQrCode,
        ]);
    }

    /**
     * Enrollment: Verify two-factor authentication code
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('enrollTwoFactorAuthentication', $user);
        if (!$user->two_factor_enabled) {
            return $this->unprocessableEntity('Two-factor authentication is not enabled.');
        }
        if ($user->two_factor_confirmed_at) {
            return $this->unprocessableEntity('Two-factor authentication is already enrolled.');
        }
        $request->validate([
            'code' => [
                'bail',
                'required',
                'string',
                'size:6',
                'regex:/^[0-9]{6}$/',
                function (string $attribute, mixed $value, Closure $fail) use ($user) {
                    if (!$user->verifyTwoFactorCode($value)) {
                        $fail('The :attribute is invalid.');
                    }
                },
            ],
        ]);
        $user->two_factor_confirmed_at = now();
        $user->save();
        return $this->noContent();
    }

    /**
     * Disable two-factor authentication
     */
    public function destroy(User $user)
    {
        $this->authorize('enrollTwoFactorAuthentication', $user);
        $user->two_factor_enabled = false;
        $user->two_factor_enabled_at = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->save();
        return $this->noContent();
    }
}
