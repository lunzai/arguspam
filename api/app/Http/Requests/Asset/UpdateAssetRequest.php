<?php

namespace App\Http\Requests\Asset;

use App\Enums\Dbms;
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
            'org_id' => ['sometimes', 'exists:App\Models\Org,id'],
            'name' => ['sometimes', 'string', 'max:100', 'min:2'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', new Enum(Status::class)],
            'host' => ['sometimes', 'string', 'max:255'],
            'port' => ['sometimes', 'integer', 'min:1', 'max:65535'],
            'dbms' => ['sometimes', new Enum(Dbms::class)],
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
        if ($this->has('dbms')) {
            $this->merge([
                'dbms' => strtolower($this->dbms),
            ]);
        }
    }
}
