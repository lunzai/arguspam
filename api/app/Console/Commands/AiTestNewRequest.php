<?php

namespace App\Console\Commands;

use App\Enums\RiskRating;
use App\Models\Request;
use App\Services\OpenAI\OpenAiService;
use Illuminate\Console\Command;

class AiTestNewRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:test:new-request {request : The request ID} {--save=false : Save the AI evaluation to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test AI evaluation for a request';

    /**
     * Execute the console command.
     */
    public function handle(OpenAiService $openAiService)
    {
        $requestId = $this->argument('request');
        $save = $this->option('save');
        
        // Convert string values to boolean
        $shouldSave = in_array($save, ['true', '1', 'yes'], true);

        $request = Request::find($requestId);
        
        if (!$request) {
            $this->error("Request with ID {$requestId} not found.");
            return 1;
        }

        $this->info("Testing AI evaluation for Request ID#{$request->id}");
        
        // Get AI evaluation
        $evaluation = $openAiService->evaluateAccessRequest($request);
        
        // Convert AI risk rating to enum and validate
        try {
            $riskRatingEnum = RiskRating::from($evaluation['output']['ai_risk_rating']);
        } catch (\ValueError $e) {
            $this->error("Invalid AI risk rating value: " . $evaluation['output']['ai_risk_rating']);
            $this->error("Expected one of: " . implode(', ', array_column(RiskRating::cases(), 'value')));
            return 1;
        }
        
        $this->newLine();
        $this->info("AI Evaluation Results:");
        $this->newLine();
        $this->info("AI Note:");
        $this->info($evaluation['output']['ai_note']);
        $this->newLine();
        $this->info("AI Risk Rating:");
        $this->info($riskRatingEnum->value);
        $this->newLine();

        if ($shouldSave) {
            // Save the evaluation results we just obtained
            $request->ai_note = $evaluation['output']['ai_note'];
            $request->ai_risk_rating = $riskRatingEnum;
            $request->save();
            $this->info("AI evaluation saved to database.");
        } else {
            $this->info("AI evaluation not saved (use --save=true to save).");
        }
         
        return 0;
    }
}