<?php

namespace App\Services\OpenAI;

use App\Models\Request;
use App\Enums\RiskRating;
use OpenAI;

class OpenAIService
{
    private $client;
    
    public function __construct()
    {
        $this->client = OpenAI::client(config('openai.api_key'));
    }

    public function evaluateAccessRequest(Request $request): array
    {
        // Get duration configuration for placeholders
        $durationConfig = config('pam.access_request.duration');
        
        // Prepare replacements for system prompt
        $systemReplacements = [
            'min' => $durationConfig['min'],
            'max' => $durationConfig['max'],
            'recommended_min' => $durationConfig['recommended_min'],
            'recommended_max' => $durationConfig['recommended_max'],
            'low_threshold' => $durationConfig['low_threshold'],
            'medium_threshold' => $durationConfig['medium_threshold'],
            'high_threshold' => $durationConfig['high_threshold'],
        ];
        
        // Prepare replacements for user prompt
        $userReplacements = array_merge($systemReplacements, [
            'database_name' => $request->asset?->name ?? 'Unknown Database',
            'start_datetime' => $request->start_datetime?->format('Y-m-d H:i:s') ?? 'N/A',
            'end_datetime' => $request->end_datetime?->format('Y-m-d H:i:s') ?? 'N/A',
            'duration' => $request->duration ?? 0,
            'reason' => $request->reason ?? 'N/A',
            'intended_query' => $request->intended_query ?? 'N/A',
            'access_scope' => $request->scope?->value ?? 'N/A',
            'is_sensitive_data' => $request->is_access_sensitive_data ? 'Yes' : 'No',
            'sensitive_data_note' => $request->sensitive_data_note ?? 'N/A',
        ]);
        
        // Load and process prompts
        $systemPrompt = $this->loadPrompt('new-request/system.md', $systemReplacements);
        $userPrompt = $this->loadPrompt('new-request/user.md', $userReplacements);
        
        // Get AI response
        $response = $this->generateCompletion($systemPrompt, $userPrompt);
        
        // Return structured result
        return [
            'ai_note' => $response['note'] ?? $response['ai_note'] ?? 'AI evaluation failed',
            'ai_risk_rating' => $this->mapRiskRating($response['risk_rating'] ?? $response['ai_risk_rating'] ?? 'Medium'),
        ];
    }

    public function generateCompletion(string $systemPrompt, string $userPrompt): array
    {
        $config = config('pam.openai');
        
        $response = $this->client->chat()->create([
            'model' => $config['model'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user', 
                    'content' => $userPrompt,
                ]
            ],
            'temperature' => $config['temperature'],
            'max_tokens' => $config['max_output_tokens'],
            'top_p' => $config['top_p'],
            'response_format' => ['type' => 'json_object'],
        ]);

        return json_decode($response->choices[0]->message->content, true);
    }

    public function loadPrompt($path, $replacements = [])
    {
        $content = file_get_contents(app_path("Services/OpenAI/prompts/{$path}"));
        
        foreach ($replacements as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }
        
        return $content;
    }

    private function mapRiskRating(string $rating): RiskRating
    {
        return match($rating) {
            'Low' => RiskRating::LOW,
            'Medium' => RiskRating::MEDIUM,
            'High' => RiskRating::HIGH,
            'Critical' => RiskRating::CRITICAL,
            default => RiskRating::MEDIUM,
        };
    }
}