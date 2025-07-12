<?php

namespace App\Http\Requests\AccessRestriction;

use App\Enums\AccessRestrictionType;
use App\Enums\Status;
use Illuminate\Validation\Rules\Enum;

class StoreAccessRestrictionRequest extends BaseAccessRestrictionRequest
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
            'name' => ['bail', 'required', 'string', 'max:100', 'min:2'],
            'description' => ['nullable', 'string'],
            'type' => ['bail', 'required', new Enum(AccessRestrictionType::class)],
            'data' => ['bail', 'required', 'array'],
            'status' => ['required', new Enum(Status::class)],
            'weight' => ['required', 'integer', 'min:0', 'default:0'],
            'data.allow' => ['required_without:data.deny', 'array', 'min:1'],
            'data.deny' => ['required_without:data.allow', 'array', 'min:1'],
        ];
        match ($this->input('type')) {
            AccessRestrictionType::IP_ADDRESS->value => $rules = array_merge($rules, $this->getIpAddressRules()),
            AccessRestrictionType::TIME_WINDOW->value => $rules = array_merge($rules, $this->getTimeWindowRules()),
            AccessRestrictionType::LOCATION->value => $rules = array_merge($rules, $this->getLocationRules()),
        };
        return $rules;
    }
}
