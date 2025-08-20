<?php

namespace App\Http\Requests\Asset;

use App\Enums\Status;
use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateAssetRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:100', 'min:2'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', new Enum(Status::class)],
        ];
    }

    public function attributes(): array
    {
        return Asset::$attributeLabels;
    }

    protected function prepareForValidation()
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower($this->status),
            ]);
        }
    }
}
