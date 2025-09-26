<?php

namespace App\Services\OpenAI;

use App\Models\Request;
use App\Models\Session;
use Exception;
use File;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Responses\CreateResponse;

class OpenAiService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('pam.openai');
    }

    public function evaluateAccessRequest(Request $request): array
    {
        $config = array_merge($this->config, config('pam.access_request.duration'));
        $systemPrompt = view('prompts.new-request.system', [
            'config' => $config,
            'request' => $request,
        ])->render();
        $userPrompt = view('prompts.new-request.user', [
            'config' => $config,
            'request' => $request,
        ])->render();
        $format = $this->getFormat('prompts/return-formats/request-evaluation.json');
        try {
            $response = $this->getResponse($systemPrompt, $userPrompt, $format);
            return $this->prepareResponse($response);
        } catch (Exception $e) {
            Log::error('Access request evaluation failed', [
                'request_id' => $request->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function auditSession(Session $session): array
    {
        // TODO: Implement session audit
        return [];
    }

    private function prepareResponse(CreateResponse $response, bool $outputJson = true): array
    {
        return [
            'id' => $response->id,
            'created_at' => $response->createdAt,
            'error' => $response->error,
            'model' => $response->model,
            'output' => $outputJson ? json_decode($response->outputText, true) : $response->outputText,
            'reasoning' => $response->reasoning,
            'store' => $response->store,
            'temperature' => $response->temperature,
            'usage' => [
                'input_tokens' => $response->usage->inputTokens,
                'cache_tokens' => $response->usage->inputTokensDetails->cachedTokens,
                'output_tokens' => $response->usage->outputTokens,
                'reasoning_tokens' => $response->usage->outputTokensDetails->reasoningTokens,
                'total_tokens' => $response->usage->totalTokens,
            ],
            'response' => $response->toArray(),
        ];
    }

    private function getResponse(string $systemPrompt, string $userPrompt, array $format = []): CreateResponse
    {
        $response = OpenAI::responses()->create([
            'model' => $this->config['model'],
            'input' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt,
                ],
            ],
            'text' => $format,
            'temperature' => $this->config['temperature'],
            'max_output_tokens' => $this->config['max_output_tokens'],
            'top_p' => $this->config['top_p'],
            'store' => $this->config['store'],
        ]);
        return $response;
    }

    private function getFormat(string $path): array
    {
        return json_decode(File::get(resource_path($path)), true);
    }
}
