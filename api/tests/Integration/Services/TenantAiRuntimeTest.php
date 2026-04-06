<?php

namespace Tests\Integration\Services;

use App\Enums\AiAgentRole;
use App\Models\Org;
use App\Models\OrgAiAgent;
use App\Models\OrgAiProvider;
use App\Services\Ai\TenantAiRuntime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TenantAiRuntimeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_uses_config_defaults_when_no_db_ai_config(): void
    {
        Config::set('ai.default', 'openai');
        Config::set('ai.defaults', [
            'generation' => [
                'model' => 'gpt-4o-mini',
                'request_timeout_seconds' => 90,
            ],
            'openai_metadata' => ['app' => 'TestApp'],
        ]);

        $org = Org::factory()->create();
        $runtime = app(TenantAiRuntime::class)->forOrgAndRole((int) $org->id, AiAgentRole::AccessRequestEvaluation);

        $this->assertSame(['openai' => 'gpt-4o-mini'], $runtime->providerChain);
        $this->assertSame(90, $runtime->timeout);
        $this->assertSame(['app' => 'TestApp'], $runtime->openaiMetadata);
    }

    #[Test]
    public function it_resolves_provider_chain_from_database(): void
    {
        Config::set('ai.providers.openai', [
            'driver' => 'openai',
            'key' => 'sk-fallback',
        ]);

        $org = Org::factory()->create();
        $provider = OrgAiProvider::factory()->create([
            'org_id' => $org->id,
            'driver' => 'openai',
            'api_key' => 'sk-tenant-key',
        ]);
        OrgAiAgent::factory()->create([
            'org_id' => $org->id,
            'role' => AiAgentRole::AccessRequestEvaluation,
            'failover' => [
                ['provider_id' => $provider->id, 'model' => 'gpt-4o'],
            ],
        ]);

        $runtime = app(TenantAiRuntime::class)->forOrgAndRole((int) $org->id, AiAgentRole::AccessRequestEvaluation);

        $expectedName = OrgAiProvider::configNameFor((int) $org->id, $provider->id);
        $this->assertSame([$expectedName => 'gpt-4o'], $runtime->providerChain);
    }
}
