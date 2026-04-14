<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $minPasswordLength = config('pam.password.min_length', 8);

        return [
            'current_password' => ['required', 'string', 'min:'.$minPasswordLength, 'current_password:api'],
            'new_password' => ['required', 'string', 'min:'.$minPasswordLength, 'confirmed:new_password_confirmation'],
            'new_password_confirmation' => ['required', 'string', 'min:'.$minPasswordLength],
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'Current Password',
            'new_password' => 'New Password',
            'new_password_confirmation' => 'New Password Confirmation',
        ];
    }
}
