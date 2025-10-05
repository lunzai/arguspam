<?php

namespace App\Http\Requests\UserAccessRestriction;

use App\Enums\AccessRestrictionType;
use App\Enums\Status;
use App\Rules\IpOrCidr;
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
        $rules = [
            'user_id' => ['sometimes', 'exists:users,id'],
            'type' => ['sometimes', new Enum(AccessRestrictionType::class)],
            'value' => ['sometimes', 'array'],
            'status' => ['sometimes', new Enum(Status::class)],
        ];

        // If we're updating the type, or if we're updating the value for an existing type
        $type = $this->type ?? $this->route('userAccessRestriction')->type->value;

        if ($this->has('value') || $this->has('type')) {
            $typeRules = $this->getValueRulesForType($type);
            if (!empty($typeRules)) {
                foreach ($typeRules as $key => $rule) {
                    // Make all nested rules 'sometimes' for updates
                    $updatedRule = array_merge(['sometimes'], is_array($rule) ? $rule : [$rule]);
                    $rules["value.{$key}"] = $updatedRule;
                }
            }
        }

        return $rules;
    }

    /**
     * Get validation rules specific to each restriction type
     */
    protected function getValueRulesForType(string $type): array
    {
        return match ($type) {
            AccessRestrictionType::IP_ADDRESS->value => [
                'allowed_ips' => ['required', 'array'],
                'allowed_ips.*' => ['required', 'string', new IpOrCidr],
            ],
            AccessRestrictionType::TIME_WINDOW->value => [
                'days' => ['required', 'array'],
                'days.*' => ['required', 'integer', 'between:0,6'], // 0=Sunday, 6=Saturday
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
                'timezone' => ['required', 'string', 'timezone'],
            ],
            AccessRestrictionType::COUNTRY->value => [
                'allowed_countries' => ['required', 'array'],
                'allowed_countries.*' => ['required', 'string', 'size:2'], // ISO country codes
            ],
            default => [],
        };
    }

    protected function prepareForValidation()
    {
        if ($this->has('status') && is_string($this->status)) {
            $this->merge([
                'status' => strtolower($this->status),
            ]);
        }
        if ($this->has('type') && is_string($this->type)) {
            $this->merge([
                'type' => strtolower($this->type),
            ]);
        }
    }
}
