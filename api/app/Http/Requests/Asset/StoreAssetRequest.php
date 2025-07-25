<?php

namespace App\Http\Requests\Asset;

use App\Enums\Dbms;
use App\Enums\Status;
use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreAssetRequest extends FormRequest
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
            'org_id' => ['required', 'exists:App\Models\Org,id'],
            'name' => ['required', 'string', 'max:100', 'min:2'],
            'description' => ['nullable', 'string'],
            'status' => ['required', new Enum(Status::class)],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:0', 'max:65535'],
            'dbms' => ['required', new Enum(Dbms::class)],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
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
