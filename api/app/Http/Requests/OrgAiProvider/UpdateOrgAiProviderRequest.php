<?php

namespace App\Http\Requests\OrgAiProvider;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Laravel\Ai\Enums\Lab;

class UpdateOrgAiProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'driver' => ['sometimes', 'string', 'max:64', Rule::in($this->allowedDrivers())],
            'label' => ['nullable', 'string', 'max:191'],
            'options' => ['nullable', 'array'],
            'api_key' => ['nullable', 'string', 'max:65535'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'is_enabled' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return list<string>
     */
    private function allowedDrivers(): array
    {
        $drivers = [];
        foreach (config('ai.providers', []) as $providerConfig) {
            if (!is_array($providerConfig)) {
                continue;
            }
            $d = $providerConfig['driver'] ?? null;
            if ($d instanceof Lab) {
                $drivers[] = $d->value;
            } elseif (is_string($d) && $d !== '') {
                $drivers[] = $d;
            }
        }

        return array_values(array_unique($drivers));
    }
}
