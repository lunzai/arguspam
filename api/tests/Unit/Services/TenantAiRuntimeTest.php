<?php

namespace Tests\Unit\Services;

use App\Enums\AiAgentRole;
use App\Services\Ai\TenantAiPromptRuntime;
use App\Services\Ai\TenantAiRuntime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantAiRuntimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_org_and_role_returns_prompt_runtime_when_no_agent_configured(): void
    {
        $runtime = new TenantAiRuntime;
        $result = $runtime->forOrgAndRole(99999, AiAgentRole::SessionReview);

        $this->assertInstanceOf(TenantAiPromptRuntime::class, $result);
        $this->assertNotEmpty($result->providerChain);
    }

    public function test_for_org_and_role_access_request_evaluation_merges_pam_config(): void
    {
        $runtime = new TenantAiRuntime;
        $result = $runtime->forOrgAndRole(99999, AiAgentRole::AccessRequestEvaluation);

        $this->assertInstanceOf(TenantAiPromptRuntime::class, $result);
    }

    public function test_prompt_runtime_dto_stores_values(): void
    {
        $dto = new TenantAiPromptRuntime(
            providerChain: ['openai' => 'gpt-4o'],
            timeout: 30,
            promptViewConfig: ['key' => 'value'],
            openaiMetadata: [],
            temperature: 0.7,
            maxOutputTokens: 1000,
        );

        $this->assertEquals(['openai' => 'gpt-4o'], $dto->providerChain);
        $this->assertEquals(30, $dto->timeout);
        $this->assertEquals(0.7, $dto->temperature);
        $this->assertEquals(1000, $dto->maxOutputTokens);
    }
}
