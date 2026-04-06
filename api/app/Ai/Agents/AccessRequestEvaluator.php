<?php

namespace App\Ai\Agents;

use App\Enums\RiskRating;
use App\Models\Request;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

class AccessRequestEvaluator implements Agent, HasProviderOptions, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        public Request $request,
        public array $config,
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
        return view('prompts.new-request.system', [
            'config' => $this->config,
            'request' => $this->request,
        ])->render();
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ai_note' => $schema->string()->min(1)->required(),
            'ai_risk_rating' => $schema->string()->enum(
                array_map(fn (RiskRating $r) => $r->value, RiskRating::cases())
            )->required(),
        ];
    }
}
