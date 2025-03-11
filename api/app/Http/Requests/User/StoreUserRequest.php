<?php

namespace App\Http\Requests\User;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:App\Models\User,email'],
            'password' => [
                'required',
                'string',
                'min:'.config('constants.password.min'),
                'confirmed:passwordConfirmation',
            ],
            'passwordConfirmation' => [
                'required',
                'string',
                'min:'.config('constants.password.min'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'passwordConfirmation' => 'Password Confirmation',
        ];
    }
}
