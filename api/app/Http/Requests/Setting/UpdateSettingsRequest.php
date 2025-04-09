<?php

namespace App\Http\Requests\Setting;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [];

        foreach ($this->keys() as $key) {
            if ($key === '_method') {
                continue;
            } // Skip Laravel method field

            // Find the setting by key_slug
            $setting = Setting::where('key_slug', $key)->first();

            if (!$setting) {
                $rules[$key] = ['prohibited'];
                continue;
            }

            // Add validation rule based on data_type
            $rules[$key] = [$this->getValidationRule($setting->data_type->value)];
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->keys() as $key) {
            if ($key === '_method') {
                continue;
            }

            $setting = Setting::where('key_slug', $key)->first();

            if ($setting) {
                $attributes[$key] = $setting->label;
            }
        }

        return $attributes;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            '*.prohibited' => 'The setting :attribute does not exist.',
        ];
    }

    /**
     * Get validation rule for a setting data type
     */
    protected function getValidationRule(string $dataType): string
    {
        return match ($dataType) {
            'boolean' => 'boolean',
            'integer' => 'integer',
            'float' => 'numeric',
            'json' => 'json',
            'array' => 'array',
            default => 'string',
        };
    }
}
