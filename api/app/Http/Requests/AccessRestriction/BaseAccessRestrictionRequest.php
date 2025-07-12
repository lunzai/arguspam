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
            
            // Common allow/deny messages
            'data.allow.array' => 'The allow field must be an array.',
            'data.deny.array' => 'The deny field must be an array.',
            
            // Time Windows messages
            'data.allow.*.days.required' => 'Days are required for each time window.',
            'data.allow.*.days.array' => 'Days must be an array.',
            'data.allow.*.days.*.integer' => 'Each day must be an integer.',
            'data.allow.*.days.*.between' => 'Days must be between 1 (Monday) and 7 (Sunday).',
            'data.allow.*.start_time.required' => 'Start time is required.',
            'data.allow.*.start_time.regex' => 'Start time must be in HH:MM format.',
            'data.allow.*.end_time.required' => 'End time is required.',
            'data.allow.*.end_time.regex' => 'End time must be in HH:MM format.',
            'data.allow.*.timezone.required' => 'Timezone is required.',
            'data.allow.*.timezone.timezone' => 'Must be a valid timezone.',
            
            // Location messages
            'data.allow.*.size' => 'Country code must be exactly 2 characters.',
            'data.allow.*.regex' => 'Country code must be uppercase letters only.',
            'data.deny.*.size' => 'Country code must be exactly 2 characters.',
            'data.deny.*.regex' => 'Country code must be uppercase letters only.',
        ];
    }

    private function getIpAddressRules(): array
    {
        return [
            'data.allow.*' => ['ip'],
            'data.deny.*' => ['ip'],
        ];
    }

    private function getTimeWindowRules(): array
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

    private function getLocationRules(): array
    {
        return [
            'data.allow.*' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'data.deny.*' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
        ];
    }
}
