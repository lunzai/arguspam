<?php

namespace App\Http\Controllers;

use App\Events\UserLoggedIn;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function me(Request $request): UserResource
    {
        return UserResource::make($request->user());
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
        $token = $user->createToken(
            'auth_token',
            ['*'],
            now()->addMinutes(60 * 24)
        )->plainTextToken;
        UserLoggedIn::dispatch($user);

        return $this->success([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): Response
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();

        return $this->ok();
    }
}
