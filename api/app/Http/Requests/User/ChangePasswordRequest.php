<?php

namespace App\Http\Requests\User;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $minPasswordLength = config('constants.password.min');
        return [
            'currentPassword' => ['required', 'string', 'min:'.$minPasswordLength, 'current_password:api'],
            'newPassword' => ['required', 'string', 'min:'.$minPasswordLength, 'confirmed:newPasswordConfirmation'],
            'newPasswordConfirmation' => ['required', 'string', 'min:'.$minPasswordLength],
        ];
    }

    public function attributes(): array
    {
        return [
            'currentPassword' => 'Current Password',
            'newPassword' => 'New Password',
            'newPasswordConfirmation' => 'New Password Confirmation',
        ];
    }
}
