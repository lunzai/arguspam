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
     */
    public function store(ResetPasswordRequest $request, User $user): Response|JsonResponse
    {
        $this->authorize('resetPasswordAny', $user);
        $validated = $request->validated();
        $user->password = Hash::make($validated['new_password']);
        $user->save();
        return $this->ok();
    }

    /**
     * Change password for the current user
     */
    public function update(ChangePasswordRequest $request): Response|JsonResponse
    {
        $validated = $request->validated();
        $user = Auth::user();
        $this->authorize('changePassword', $user);

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
