<?php

namespace App\Http\Requests\AccessRestriction;

use Illuminate\Foundation\Http\FormRequest;

class BaseAccessRestrictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages()
    {
        return [
            'type.in' => 'The type must be one of: ip_address, time_windows, location.',
            'data.required' => 'The data field is required.',
            'data.array' => 'The data field must be an array.',

            'data.allow.required_without' => 'The allow field is required when deny is not present.',
            'data.deny.required_without' => 'The deny field is required when allow is not present.',

            // Common allow/deny messages
            'data.allow.array' => 'The allow field must be an array.',
            'data.deny.array' => 'The deny field must be an array.',

            // IP Address messages
            'data.*.*.ip' => 'The allow field must be an array of IP addresses.',

            // Time Windows messages
            'data.*.*.day_of_week.required' => 'Days are required for each time window.',
            'data.*.*.day_of_week.array' => 'Days must be an array.',
            'data.*.*.day_of_week.*.integer' => 'Each day must be an integer.',
            'data.*.*.day_of_week.*.between' => 'Days must be between 1 (Monday) and 7 (Sunday).',
            'data.*.*.start_time.required' => 'Start time is required.',
            'data.*.*.start_time.date_format' => 'Start time must be in HH:MM format.',
            'data.*.*.end_time.required' => 'End time is required.',
            'data.*.*.end_time.date_format' => 'End time must be in HH:MM format.',
            'data.*.*.end_time.after' => 'End time must be after start time.',
            'data.*.*.timezone.required' => 'Timezone is required.',
            'data.*.*.timezone.timezone' => 'Must be a valid timezone.',
            
            // Country messages
            'data.*.*.size' => 'Country code must be exactly 2 characters.',
            'data.*.*.regex' => 'Country code must be uppercase letters only.',
        ];
    }

    protected function getIpAddressRules(): array
    {
        return [
            'data.allow.*' => ['ip'],
            'data.deny.*' => ['ip'],
        ];
    }

    protected function getTimeWindowRules(): array
    {
        return [
            'data.allow.*.day_of_week' => ['required', 'array', 'min:1'],
            'data.allow.*.day_of_week.*' => ['required', 'integer', 'between:1,7'],
            'data.allow.*.start_time' => ['required', 'date_format:H:i'],
            'data.allow.*.end_time' => ['required', 'date_format:H:i', 'after:data.allow.*.start_time'],
            'data.allow.*.timezone' => ['required', 'timezone'],

            'data.deny.*.day_of_week' => ['required', 'array', 'min:1'],
            'data.deny.*.day_of_week.*' => ['required', 'integer', 'between:1,7'],
            'data.deny.*.start_time' => ['required', 'date_format:H:i'],
            'data.deny.*.end_time' => ['required', 'date_format:H:i', 'after:data.deny.*.start_time'],
            'data.deny.*.timezone' => ['required', 'timezone'],
        ];
    }

    protected function getCountryRules(): array
    {
        return [
            'data.allow.*' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'data.deny.*' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
        ];
    }
}
