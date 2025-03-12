<?php

namespace App\Http\Requests\UserAccessRestriction;

use App\Enums\RestrictionType;
use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreUserAccessRestrictionRequest extends FormRequest
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
        // TODO: refine value array validation
        return [
            'user_id' => ['required', 'exists:App\Models\User,id'],
            'type' => ['required', new Enum(RestrictionType::class)],
            'value' => ['required', 'array'],
            'status' => ['required', new Enum(Status::class)],
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
