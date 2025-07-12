<?php

namespace App\Http\Requests\AccessRestriction;

use App\Enums\AccessRestrictionType;
use App\Enums\Status;
use Illuminate\Validation\Rules\Enum;

class UpdateAccessRestrictionRequest extends BaseAccessRestrictionRequest
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
        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:100', 'min:2'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'required', new Enum(AccessRestrictionType::class)],
            'data' => ['sometimes', 'required', 'array'],
            'status' => ['sometimes', new Enum(Status::class)],
            'weight' => ['sometimes', 'integer', 'min:0', 'default:0'],
            'data.allow' => ['sometimes', 'array'],
            'data.deny' => ['sometimes', 'array'],
        ];
        match ($this->input('type')) {
            AccessRestrictionType::IP_ADDRESS->value => $rules = array_merge($rules, $this->getIpAddressRules()),
            AccessRestrictionType::TIME_WINDOW->value => $rules = array_merge($rules, $this->getTimeWindowRules()),
            AccessRestrictionType::LOCATION->value => $rules = array_merge($rules, $this->getLocationRules()),
        };
        return $rules;
    }
}
