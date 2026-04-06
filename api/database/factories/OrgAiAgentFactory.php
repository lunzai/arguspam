<?php

namespace Database\Factories;

use App\Enums\AiAgentRole;
use App\Models\Org;
use App\Models\OrgAiAgent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrgAiAgent>
 */
class OrgAiAgentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'org_id' => Org::factory(),
            'role' => AiAgentRole::AccessRequestEvaluation,
            'failover' => [],
            'temperature' => 0.2,
            'max_output_tokens' => 2048,
            'request_timeout_seconds' => 120,
            'provider_metadata' => ['app' => 'Test'],
        ];
    }
}
