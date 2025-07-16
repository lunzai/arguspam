<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Reset password for any user
     *
     * @param ResetPasswordRequest $request
     * @return Response|JsonResponse
     */
    public function store(ResetPasswordRequest $request, User $user): Response|JsonResponse
    {
        $this->authorize('password:resetany');
        $validated = $request->validated();
        $user->password = Hash::make($validated['new_password']);
        $user->save();
        return $this->ok();
    }

    /**
     * Change password for the current user
     *
     * @param ChangePasswordRequest $request
     * @return Response|JsonResponse
     */
    public function update(ChangePasswordRequest $request): Response|JsonResponse
    {
        $this->authorize('password:change');
        $validated = $request->validated();

        /** @var User $user */
        $user = Auth::user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['The current password is incorrect.'],
                ],
            ], 422);
        }

        // Update password
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return $this->ok();
    }
}
