<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
                Password::min(8),
            ],
            'new_password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8),
                // ->letters()
                // ->mixedCase()
                // ->numbers()
                // ->symbols(),
                'different:current_password',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.confirmed' => 'Password confirmation does not match.',
            'new_password.min' => 'New password must be at least 8 characters.',
            'new_password.different' => 'New password must be different from current password.',
        ];
    }
}
