<?php

namespace App\Services\Ai;

readonly class TenantAiPromptRuntime
{
    /**
     * @param  array<string, string>  $providerChain
     * @param  array<string, mixed>  $promptViewConfig
     * @param  array<string, mixed>  $openaiMetadata
     */
    public function __construct(
        public array $providerChain,
        public int $timeout,
        public array $promptViewConfig,
        public array $openaiMetadata,
        public ?float $temperature,
        public ?int $maxOutputTokens,
    ) {}
}
