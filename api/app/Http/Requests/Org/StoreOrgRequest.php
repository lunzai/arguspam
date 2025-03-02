<?php

namespace App\Http\Requests\Org;

use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Models\Org;

class StoreOrgRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100', 'min:2'],
            'description' => ['nullable', 'string'],
            'status' => ['required', new Enum(Status::class)],
        ];
    }

    public function attributes(): array
    {
        return Org::$attributeLabels;
    }
}
