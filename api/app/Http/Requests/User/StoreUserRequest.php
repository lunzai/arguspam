<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:App\Models\User,email'],
            'password' => [
                'required',
                'string',
                'min:'.config('pam.password.min_length', 8),
                'confirmed:password_confirmation',
            ],
            'password_confirmation' => [
                'required',
                'string',
                'min:'.config('pam.password.min_length', 8),
            ],
            'two_factor_enabled' => ['sometimes', 'boolean'],
            'default_timezone' => ['required', 'timezone'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Password Confirmation',
        ];
    }
}
