<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponses;
use App\Events\UserLoggedIn;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use ApiResponses;

    public function test()
    {
        return $this->success('Hello successful', ['token' => '123']);
    }

    public function login(LoginRequest $request)
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
            now()->addMinutes(60*24)
        )->plainTextToken;
        UserLoggedIn::dispatch($user);
        Log::info('About to dispatch UserLoggedIn');

        return $this->success(['token' => $token], 'Login successful');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();

        return $this->ok('Logged out successfully');
    }
}
