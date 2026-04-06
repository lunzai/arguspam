<?php

namespace App\Ai\Agents;

use App\Enums\RiskRating;
use App\Enums\SessionFlag;
use App\Models\Session;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

class SessionReviewAgent implements Agent, HasProviderOptions, HasStructuredOutput
{
    use Promptable;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        public Session $session,
        public array $config = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function providerOptions(Lab|string $provider): array
    {
        if ($provider !== Lab::OpenAI && $provider !== 'openai') {
            return [];
        }

        $metadata = $this->config['openai_metadata'] ?? [];

        return is_array($metadata) && $metadata !== []
            ? ['metadata' => $metadata]
            : [];
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return view('prompts.session-review.system', [
            'session' => $this->session,
        ])->render();
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        $riskValues = array_map(fn (RiskRating $r) => $r->value, RiskRating::cases());
        $flagValues = array_map(fn (SessionFlag $f) => $f->value, SessionFlag::cases());

        return [
            'ai_note' => $schema->string()->min(1)->required(),
            'session_activity_risk' => $schema->string()->enum($riskValues)->required(),
            'deviation_risk' => $schema->string()->enum($riskValues)->required(),
            'overall_risk' => $schema->string()->enum($riskValues)->required(),
            'flags' => $schema->array()->items(
                $schema->string()->enum($flagValues)
            )->required(),
            'human_audit_confidence' => $schema->integer()->min(0)->max(100)->required(),
            'human_audit_required' => $schema->boolean()->required(),
        ];
    }
}
