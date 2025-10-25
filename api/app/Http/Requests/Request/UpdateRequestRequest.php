<?php

namespace App\Http\Requests\Request;

use App\Enums\DatabaseScope;
use App\Enums\RequestStatus;
use App\Enums\RiskRating;
use App\Models\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateRequestRequest extends FormRequest
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
            'org_id' => ['sometimes', 'exists:App\Models\Org,id'],
            'asset_id' => ['sometimes', 'exists:App\Models\Asset,id'],
            'asset_account_id' => ['sometimes', 'nullable', 'exists:App\Models\AssetAccount,id'],
            'requester_id' => ['sometimes', 'exists:App\Models\User,id'],
            'start_datetime' => [
                'sometimes',
                'date',
                'after:now',
            ],
            'end_datetime' => [
                'sometimes',
                'date',
                'after:start_datetime',
            ],
            'duration' => [
                'sometimes',
                'integer',
                'min:'.config('pam.access_request.duration.min'),
                'max:'.config('pam.access_request.duration.max'),
            ],
            'reason' => ['sometimes', 'string', 'max:255'],
            'intended_query' => ['sometimes', 'nullable', 'string'],
            'scope' => ['sometimes', new Enum(DatabaseScope::class)],
            'is_access_sensitive_data' => ['sometimes', 'boolean'],
            'sensitive_data_note' => [
                'sometimes',
                'nullable',
                'string',
                'required_if:is_access_sensitive_data,true',
            ],
            'ai_note' => ['sometimes', 'nullable', 'string'],
            'ai_risk_rating' => ['sometimes', 'nullable', new Enum(RiskRating::class)],
            'status' => ['sometimes', new Enum(RequestStatus::class)],
        ];
    }

    public function attributes(): array
    {
        return Request::$attributeLabels;
    }

    protected function prepareForValidation()
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower($this->status),
            ]);
        }
        if ($this->has('scope')) {
            $this->merge([
                'scope' => strtolower($this->scope),
            ]);
        }
    }
}
