<?php

namespace App\Services\Ai;

use App\Enums\AiAgentRole;
use App\Models\OrgAiAgent;
use App\Models\OrgAiProvider;
use Illuminate\Support\Facades\Config;
use Laravel\Ai\Enums\Lab;

class TenantAiRuntime
{
    public function forOrgAndRole(int $orgId, AiAgentRole $role): TenantAiPromptRuntime
    {
        $defaults = config('ai.defaults', []);
        $agent = OrgAiAgent::query()
            ->where('org_id', $orgId)
            ->where('role', $role->value)
            ->first();

        if ($agent instanceof OrgAiAgent && is_array($agent->failover) && $agent->failover !== []) {
            $resolved = $this->resolveFromDatabase($orgId, $agent, $defaults);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return $this->resolveFromConfigDefaults($role, $defaults);
    }

    /**
     * @param  array<string, mixed>  $defaults
     */
    private function resolveFromDatabase(int $orgId, OrgAiAgent $agent, array $defaults): ?TenantAiPromptRuntime
    {
        $chain = [];
        foreach ($agent->failover as $entry) {
            $providerId = $entry['provider_id'] ?? null;
            $model = $entry['model'] ?? null;
            if (!is_int($providerId) && !is_numeric($providerId)) {
                continue;
            }
            if (!is_string($model) || $model === '') {
                continue;
            }
            $providerId = (int) $providerId;
            $provider = OrgAiProvider::query()
                ->where('org_id', $orgId)
                ->where('id', $providerId)
                ->where('is_enabled', true)
                ->first();
            if (!$provider instanceof OrgAiProvider) {
                continue;
            }
            $name = OrgAiProvider::configNameFor($orgId, $provider->id);
            $this->registerProviderConfig($name, $provider);
            $chain[$name] = $model;
        }

        if ($chain === []) {
            return null;
        }

        $metadata = array_merge(
            $defaults['openai_metadata'] ?? [],
            $agent->provider_metadata ?? [],
        );

        return new TenantAiPromptRuntime(
            providerChain: $chain,
            timeout: (int) ($agent->request_timeout_seconds ?? $defaults['generation']['request_timeout_seconds'] ?? 120),
            promptViewConfig: $this->buildPromptViewConfig($agent->role, $metadata),
            openaiMetadata: $metadata,
            temperature: $agent->temperature ?? ($defaults['generation']['temperature'] ?? null),
            maxOutputTokens: $agent->max_output_tokens ?? ($defaults['generation']['max_output_tokens'] ?? null),
        );
    }

    /**
     * @param  array<string, mixed>  $defaults
     */
    private function resolveFromConfigDefaults(AiAgentRole $role, array $defaults): TenantAiPromptRuntime
    {
        $defaultProvider = config('ai.default');
        if ($defaultProvider instanceof Lab) {
            $defaultProvider = $defaultProvider->value;
        }
        if (!is_string($defaultProvider) || $defaultProvider === '') {
            $defaultProvider = 'openai';
        }

        $model = $defaults['generation']['model'] ?? 'gpt-4o-mini';
        $chain = [$defaultProvider => $model];

        $metadata = $defaults['openai_metadata'] ?? [];

        return new TenantAiPromptRuntime(
            providerChain: $chain,
            timeout: (int) ($defaults['generation']['request_timeout_seconds'] ?? 120),
            promptViewConfig: $this->buildPromptViewConfig($role, $metadata),
            openaiMetadata: $metadata,
            temperature: $defaults['generation']['temperature'] ?? null,
            maxOutputTokens: $defaults['generation']['max_output_tokens'] ?? null,
        );
    }

    /**
     * @param  array<string, mixed>  $openaiMetadata
     * @return array<string, mixed>
     */
    private function buildPromptViewConfig(AiAgentRole $role, array $openaiMetadata): array
    {
        $base = ['openai_metadata' => $openaiMetadata];

        if ($role === AiAgentRole::AccessRequestEvaluation) {
            return array_merge(config('pam.access_request.duration', []), $base);
        }

        return $base;
    }

    private function registerProviderConfig(string $name, OrgAiProvider $provider): void
    {
        $driverKey = $provider->driver;
        $base = config("ai.providers.{$driverKey}", []);
        if (!is_array($base)) {
            $base = [];
        }

        $merged = array_merge(
            $base,
            [
                'driver' => $driverKey,
                'key' => $provider->api_key,
            ],
            is_array($provider->options) ? $provider->options : [],
        );

        Config::set("ai.providers.{$name}", $merged);
    }
}
