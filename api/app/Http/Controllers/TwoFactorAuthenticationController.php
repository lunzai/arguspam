<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Closure;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(User $user)
    {
        # TODO: Re-evaluate authorization in all controllers
        // $this->authorize('twofactorauthentication:create', $user);
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
     * Display the specified resource.
     */
    public function show(User $user)
    {
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
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
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->two_factor_enabled = false;
        $user->two_factor_enabled_at = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->save();
        return $this->noContent();
    }
}
