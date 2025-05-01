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
            'user_id' => ['sometimes', 'exists:App\Models\User,id'],
            'type' => ['sometimes', new Enum(RestrictionType::class)],
            'value' => ['sometimes', 'array'],
            // 'value' => ['sometimes', 'array', function ($attribute, $value, $fail) {
            //         if ($this->type === RestrictionType::IP && !filter_var($value, FILTER_VALIDATE_IP)) {
            //             $fail('Invalid IP address format');
            //         }
            //     }
            // ],
            'status' => ['sometimes', new Enum(Status::class)],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower($this->status),
            ]);
        }
        if ($this->has('type')) {
            $this->merge([
                'type' => strtolower($this->type),
            ]);
        }
    }
}
