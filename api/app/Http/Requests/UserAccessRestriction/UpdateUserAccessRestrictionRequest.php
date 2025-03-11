<?php

namespace App\Http\Requests\UserAccessRestriction;

use App\Enums\RestrictionType;
use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateUserAccessRestrictionRequest extends FormRequest
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
            'user_id' => ['required', 'exists:App\Models\User,id'],
            'type' => ['required', new Enum(RestrictionType::class)],
            'value' => ['required', 'array'],
            'status' => ['required', new Enum(Status::class)],
        ];
    }
}
