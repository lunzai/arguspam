<?php

namespace App\Http\Requests\UserAccessRestriction;

use App\Enums\RestrictionType;
use App\Enums\Status;
use App\Rules\IpOrCidr;
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
        $rules = [
            'type' => ['required', new Enum(RestrictionType::class)],
            'value' => ['required', 'array'],
            'status' => ['required', new Enum(Status::class)],
        ];

        // Add specific validation rules based on restriction type
        if ($this->filled('type')) {
            $typeRules = $this->getValueRulesForType($this->type);
            if (!empty($typeRules)) {
                foreach ($typeRules as $key => $rule) {
                    $rules["value.{$key}"] = $rule;
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
            RestrictionType::IP_ADDRESS->value => [
                'allowed_ips' => ['required', 'array'],
                'allowed_ips.*' => ['required', 'string', new IpOrCidr],
            ],
            RestrictionType::TIME_WINDOW->value => [
                'days' => ['required', 'array'],
                'days.*' => ['required', 'integer', 'between:0,6'], // 0=Sunday, 6=Saturday
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
                'timezone' => ['required', 'string', 'timezone'],
            ],
            RestrictionType::LOCATION->value => [
                'allowed_countries' => ['required', 'array'],
                'allowed_countries.*' => ['required', 'string', 'size:2'], // ISO country codes
            ],
            RestrictionType::DEVICE->value => [
                'allowed_devices' => ['required', 'array'],
                'allowed_devices.*' => ['required', 'string'],
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
