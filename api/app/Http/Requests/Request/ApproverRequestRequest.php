<?php

namespace App\Http\Requests\Request;

use App\Enums\RequestScope;
use App\Enums\RiskRating;
use App\Models\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ApproverRequestRequest extends FormRequest
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
        return [
            'start_datetime' => [
                'date',
                'after:now',
            ],
            'end_datetime' => [
                'date',
                'after:start_datetime',
            ],
            'duration' => [
                'integer',
                'min:'.config('pam.access_request.duration.min'),
                'max:'.config('pam.access_request.duration.max'),
            ],
            'scope' => [new Enum(RequestScope::class)],
            'approver_note' => ['string'],
            'approver_risk_rating' => [new Enum(RiskRating::class)],
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
