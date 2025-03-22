<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
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
        // TODO: only admin can update email
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:App\Models\User,email'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        // TODO: modify to exclude current user from removing their own admin status
        // $validator->after(function ($validator) {
        //     if ($this->user()->id === $this->route('user')->id) {
        //         $validator->errors()->add('email', 'Admin email cannot be updated.');
        //     }
        // });
    }
}
