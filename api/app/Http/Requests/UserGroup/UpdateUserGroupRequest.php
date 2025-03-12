<?php

namespace App\Http\Requests\UserGroup;

use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateUserGroupRequest extends FormRequest
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
            'org_id' => ['sometimes', 'exists:App\Models\Org,id'],
            'name' => ['sometimes', 'string', 'max:100', 'min:2'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', new Enum(Status::class)],
        ];
    }
}
