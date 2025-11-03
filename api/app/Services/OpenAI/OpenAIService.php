<?php

namespace App\Services\OpenAI;

use App\Models\Request;
use App\Models\Session;
use App\Services\OpenAI\Responses\RequestEvaluation;
use App\Services\OpenAI\Responses\SessionAudit;
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

    public function auditSession(Session $session): array
    {
        $systemPrompt = view('prompts.session-review.system', [
            'session' => $session,
        ])->render();
        $userPrompt = view('prompts.session-review.user', [
            'session' => $session,
        ])->render();
        $format = $this->getFormat('prompts/return-formats/session-review.json');
        try {
            $response = $this->getResponse($systemPrompt, $userPrompt, $format, [
                'type' => 'session_review',
                'ref_id' => $session->id,
                'org_id' => $session->org_id,
            ]);
            return $this->prepareResponse($response, SessionAudit::class);
        } catch (Exception $e) {
            Log::error('Session review failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
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
            $response = $this->getResponse($systemPrompt, $userPrompt, $format, [
                'type' => 'request_evaluation',
                'ref_id' => $request->id,
                'org_id' => $request->org_id,
            ]);
            return $this->prepareResponse($response, RequestEvaluation::class);
        } catch (Exception $e) {
            Log::error('Access request evaluation failed', [
                'request_id' => $request->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    // TODO: Record the response in DB
    private function prepareResponse(CreateResponse $response, string $responseClassName): array
    {
        $outputJson = json_decode($response->outputText, true);
        $outputObject = $responseClassName::fromJson($response->outputText);
        return [
            'id' => $response->id,
            'created_at' => $response->createdAt,
            'error' => $response->error,
            'model' => $response->model,
            'output_json' => $outputJson,
            'output_object' => $outputObject,
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

    private function getResponse(string $systemPrompt, string $userPrompt, array $format = [], array $metadata = []): CreateResponse
    {
        $metadata = array_map(fn ($value) => e($value), array_merge(
            $this->config['metadata'] ?? [],
            $metadata
        ));
        $config = [
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
            'metadata' => $metadata,
        ];
        Log::info('OpenAI config', ['config' => $config]);
        if (str_starts_with($config['model'], 'gpt-5')) {
            unset($config['temperature'], $config['top_p']);
            $config['service_tier'] = 'flex';
            $config['reasoning']['effort'] = 'low';
            $config['text']['verbosity'] = 'low';
        }
        $response = OpenAI::responses()->create($config);
        return $response;
    }

    private function getFormat(string $path): array
    {
        return json_decode(File::get(resource_path($path)), true);
    }
}
