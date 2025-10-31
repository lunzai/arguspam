<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Events\UserLoggedIn;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\MeResource;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function me(Request $request): MeResource
    {
        $user = $request->user();
        $user
            ->loadMissing(
                'orgs',
                'userGroups',
                'restrictions',
                'roles',
            )
            ->loadCount('scheduledSessions', 'submittedRequests');
        return MeResource::make($user);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $request->validated();
        $attempt = Auth::attemptWhen(
            $request->only('email', 'password'),
            function (User $user) {
                return $user->isActive();
            }
        );
        if (!$attempt) {
            return $this->unauthorized('The provided credentials are incorrect.');
        }
        $user = User::where('email', $request->email)->first();
        $data = [
            'user' => $user,
            'token' => null,
            'requires_2fa' => false,
            'temp_key' => null,
            'temp_key_expires_at' => null,
        ];
        $isTwoFactorConfirmed = $user->isTwoFactorConfirmed();
        if ($isTwoFactorConfirmed) {
            $tempKey = Str::random(32);
            $expiresAt = now()->addMinutes((int) config('pam.auth.temp_key_expiration', 5));
            Cache::put(
                CacheKey::AUTH_2FA_TEMP_KEY->key($tempKey),
                $user->id,
                $expiresAt
            );
            $data['requires_2fa'] = true;
            $data['temp_key'] = $tempKey;
            $data['temp_key_expires_at'] = $expiresAt;
        } else {
            $data['token'] = $user->createToken(
                'auth_token',
                ['*'],
                now()->addMinutes((int) config('sanctum.expiration', 1440))
            )->plainTextToken;
            UserLoggedIn::dispatch($user);
        }
        return $this->success($data);
    }

    public function verifyTwoFactor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'temp_key' => ['bail', 'required', 'string', 'size:32', 'regex:/^[a-zA-Z0-9]{32}$/'],
            'code' => ['bail', 'required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);
        $userId = Cache::get(
            CacheKey::AUTH_2FA_TEMP_KEY->key($validated['temp_key'])
        );
        if (!$userId) {
            return $this->unauthorized('The provided temp key is invalid.');
        }
        $user = User::find($userId);
        if (!$user->isTwoFactorConfirmed()) {
            return $this->unauthorized('Two-factor authentication is not enrolled.');
        }
        $request->validate([
            'code' => [
                function (string $attribute, mixed $value, Closure $fail) use ($user) {
                    if (!$user->verifyTwoFactorCode($value)) {
                        $fail('The :attribute is invalid.');
                    }
                },
            ],
        ]);
        Cache::forget(
            CacheKey::AUTH_2FA_TEMP_KEY->key($validated['temp_key'])
        );
        $token = $user->createToken(
            'auth_token',
            ['*'],
            now()->addMinutes((int) config('sanctum.expiration', 1440))
        )->plainTextToken;
        UserLoggedIn::dispatch($user);
        return $this->success([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();
        return $this->ok();
    }
}
