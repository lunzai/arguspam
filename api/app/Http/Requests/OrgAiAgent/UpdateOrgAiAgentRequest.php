<?php

namespace App\Http\Requests\OrgAiAgent;

use App\Enums\AiAgentRole;
use App\Models\OrgAiAgent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrgAiAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $orgId = (int) $this->get(config('pam.org.request_attribute'));
        /** @var OrgAiAgent $agent */
        $agent = $this->route('org_ai_agent');

        return [
            'role' => [
                'sometimes',
                Rule::enum(AiAgentRole::class),
                Rule::unique('org_ai_agents', 'role')
                    ->where('org_id', $orgId)
                    ->ignore($agent->id),
            ],
            'failover' => ['sometimes', 'array', 'min:1'],
            'failover.*.provider_id' => [
                'required',
                'integer',
                Rule::exists('org_ai_providers', 'id')->where('org_id', $orgId),
            ],
            'failover.*.model' => ['required', 'string', 'max:191'],
            'temperature' => ['nullable', 'numeric', 'between:0,2'],
            'max_output_tokens' => ['nullable', 'integer', 'min:1', 'max:2147483647'],
            'request_timeout_seconds' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'provider_metadata' => ['nullable', 'array'],
        ];
    }
}
