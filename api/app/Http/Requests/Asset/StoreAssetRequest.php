<?php

namespace App\Http\Requests\Asset;

use App\Enums\Dbms;
use App\Enums\Status;
use App\Models\Asset;
use App\Services\Secrets\SecretsManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class StoreAssetRequest extends FormRequest
{
    protected SecretsManager $secretManager;

    public function __construct(SecretsManager $secretManager)
    {
        $this->secretManager = $secretManager;
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

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['password', 'username'])) {
                    return;
                }
                try {
                    $this->secretManager->getDatabaseDriver(new Asset([
                        'org_id' => $this->org_id,
                        'name' => $this->name,
                        'description' => $this->description,
                        'status' => $this->status,
                        'host' => $this->host,
                        'port' => $this->port,
                        'dbms' => $this->dbms,
                    ]), [
                        'password' => $this->password,
                        'username' => $this->username,
                    ]);
                } catch (\Exception $e) {
                    $validator->errors()
                        ->add('password', $e->getMessage());
                }
            },
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
