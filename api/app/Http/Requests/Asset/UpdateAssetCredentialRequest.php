<?php

namespace App\Http\Requests\Asset;

use App\Enums\Dbms;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Services\Jit\JitManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class UpdateAssetCredentialRequest extends FormRequest
{
    protected $jitManager;

    public function __construct(JitManager $jitManager)
    {
        $this->jitManager = $jitManager;
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
            'host' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (
                        !filter_var($value, FILTER_VALIDATE_IP) &&
                        !preg_match('/^(?=.{1,253}$)(?:(?!\d+\.)[a-zA-Z0-9_](?:[a-zA-Z0-9-_]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/', $value) &&
                        !preg_match('/^localhost$/i', $value)
                    ) {
                        $fail('Invalid IP or hostname.');
                    }
                },
            ],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'dbms' => ['required', new Enum(Dbms::class)],
            'username' => ['sometimes', 'nullable', 'required_with:password', 'string', 'max:255'],
            'password' => ['sometimes', 'nullable', 'required_with:username', 'string', 'max:255'],
        ];
    }

    // public function after(): array
    // {
    //     return [
    //         function (Validator $validator) {
    //             $adminAccount = $this->route('asset')->adminAccount;
    //             if ($validator->errors()->hasAny(['password', 'username']) || !$adminAccount) {
    //                 return;
    //             }
    //             try {
    //                 $this->jitManager->testConnection(new Asset([
    //                     'host' => $this->host,
    //                     'port' => $this->port,
    //                     'dbms' => $this->dbms,
    //                 ]), [
    //                     'password' => $this->password ?? $adminAccount->password,
    //                     'username' => $this->username ?? $adminAccount->username,
    //                 ]);
    //             } catch (\Exception $e) {
    //                 $validator->errors()
    //                     ->add('password', $e->getMessage());
    //             }
    //         },
    //     ];
    // }

    public function attributes(): array
    {
        return AssetAccount::$attributeLabels;
    }

    protected function prepareForValidation()
    {
        if ($this->has('dbms')) {
            $this->merge([
                'dbms' => strtolower($this->dbms),
            ]);
        }
    }
}
