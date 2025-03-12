<?php

namespace App\Http\Requests\Request;

use App\Enums\RequestScope;
use App\Enums\RequestStatus;
use App\Enums\RiskRating;
use App\Models\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // TODO: add validation for user's and group's asset and asset account
        return [
            'org_id' => ['required', 'exists:App\Models\Org,id'],
            'asset_id' => ['required', 'exists:App\Models\Asset,id'],
            'asset_account_id' => ['nullable', 'exists:App\Models\AssetAccount,id'],
            'requester_id' => ['required', 'exists:App\Models\User,id'],
            'start_datetime' => [
                'required',
                'date',
                'after:now',
            ],
            'end_datetime' => [
                'required',
                'date',
                'after:start_datetime',
            ],
            'duration' => [
                'required',
                'integer',
                'min:'.config('pam.request.duration.min'),
                'max:'.config('pam.request.duration.max'),
            ],
            'reason' => ['required', 'string', 'max:255'],
            'intended_query' => ['nullable', 'string'],
            'scope' => ['required', new Enum(RequestScope::class)],
            'is_access_sensitive_data' => ['required', 'boolean'],
            'sensitive_data_note' => [
                'nullable',
                'string',
                'required_if:is_access_sensitive_data,true',
            ],
            'ai_note' => ['nullable', 'string'],
            'ai_risk_rating' => ['nullable', new Enum(RiskRating::class)],
            'status' => ['required', new Enum(RequestStatus::class)],
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
