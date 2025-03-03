<?php

namespace App\Http\Requests\AssetAccount;

use App\Models\AssetAccount;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetAccountRequest extends FormRequest
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
            'asset_id' => ['required', 'exists:App\Models\Asset,id'],
            'name' => ['required', 'string', 'max:100', 'min:2'],
            'vault_path' => ['nullable', 'string'],
            'is_default' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return AssetAccount::$attributeLabels;
    }
}
