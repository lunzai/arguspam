<?php

namespace App\Console\Commands;

use App\Ai\Agents\AccessRequestEvaluator;
use App\Enums\AiAgentRole;
use App\Enums\RiskRating;
use App\Models\Request;
use App\Services\Ai\TenantAiRuntime;
use Illuminate\Console\Command;

class AiTestNewRequest extends Command
{
    protected $signature = 'ai:test:new-request {request : The request ID} {--save=false : Save the AI evaluation to the database}';

    protected $description = 'Test AI evaluation for a request';

    public function handle(): int
    {
        $requestId = $this->argument('request');
        $shouldSave = in_array($this->option('save'), ['true', '1', 'yes'], true);

        $request = Request::find($requestId);

        if (!$request) {
            $this->error("Request with ID {$requestId} not found.");

            return 1;
        }

        $this->info("Testing AI evaluation for Request ID#{$request->id}");

        $runtime = app(TenantAiRuntime::class)->forOrgAndRole(
            (int) $request->org_id,
            AiAgentRole::AccessRequestEvaluation,
        );
        $config = $runtime->promptViewConfig;
        $agent = new AccessRequestEvaluator($request, $config);
        $userPrompt = view('prompts.new-request.user', [
            'config' => $config,
            'request' => $request,
        ])->render();

        $response = $agent->prompt(
            $userPrompt,
            provider: $runtime->providerChain,
            model: null,
            timeout: $runtime->timeout,
        );
        $evaluation = $response->toArray();

        try {
            $riskRatingEnum = RiskRating::from($evaluation['ai_risk_rating']);
        } catch (\ValueError $e) {
            $this->error('Invalid AI risk rating value: '.$evaluation['ai_risk_rating']);
            $this->error('Expected one of: '.implode(', ', array_column(RiskRating::cases(), 'value')));

            return 1;
        }

        $this->newLine();
        $this->info('AI Evaluation Results:');
        $this->newLine();
        $this->info('AI Note:');
        $this->info($evaluation['ai_note']);
        $this->newLine();
        $this->info('AI Risk Rating:');
        $this->info($riskRatingEnum->value);
        $this->newLine();

        if ($shouldSave) {
            $request->applyAiEvaluation($evaluation);
            $this->info('AI evaluation saved to database.');
        } else {
            $this->info('AI evaluation not saved (use --save=true to save).');
        }

        return 0;
    }
}
