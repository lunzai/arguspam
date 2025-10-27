<?php

namespace App\Http\Requests\Request;

use App\Enums\DatabaseScope;
use App\Enums\RiskRating;
use App\Models\Request;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ApproveRequestRequest extends FormRequest
{
    protected $durationMin;
    protected $durationMax;

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->durationMin = intval(config('pam.access_request.duration.min', 10));
        $this->durationMax = intval(config('pam.access_request.duration.max', 43200));
    }
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
            'start_datetime' => [
                'required',
                'date',
            ],
            'end_datetime' => [
                'required',
                'date',
                'after:start_datetime',
                'after:now',
            ],
            'duration' => [
                // 'required',
                'integer',
                'min:'.$this->durationMin,
                'max:'.$this->durationMax,
            ],
            'scope' => ['required', new Enum(DatabaseScope::class)],
            'approver_note' => ['required', 'string'],
            'approver_risk_rating' => ['required', new Enum(RiskRating::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'duration.min' => sprintf(
                'The duration must be at least %s.',
                Carbon::now()
                    ->addMinutes($this->durationMin + 1)
                    ->longAbsoluteDiffForHumans()
            ),
            'duration.max' => sprintf(
                'The duration must be less than %s.',
                Carbon::now()
                    ->addMinutes($this->durationMax + 1)
                    ->longAbsoluteDiffForHumans()
            ),
            'end_datetime.after' => 'The end datetime must be after :date.',
        ];
    }

    public function attributes(): array
    {
        return Request::$attributeLabels;
    }

    protected function prepareForValidation()
    {
        if ($this->has('start_datetime') && $this->has('end_datetime')) {
            try {
                $start = Carbon::parse($this->start_datetime);
                $end = Carbon::parse($this->end_datetime);
                $this->merge([
                    'duration' => $start->diffInMinutes($end),
                ]);
            } catch (\Exception $e) {
                // do nothing
            }
        }
    }
}
