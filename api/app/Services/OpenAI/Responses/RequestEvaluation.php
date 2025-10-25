<?php

namespace App\Services\OpenAI\Responses;

use App\Enums\RiskRating;

class RequestEvaluation extends BaseResponse
{
    public function __construct(
        public string $aiNote,
        public RiskRating $aiRiskRating,
    ) {}

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: '.json_last_error_msg());
        }
        return new self(
            $data['ai_note'],
            RiskRating::from($data['ai_risk_rating']),
        );
    }

    public function toArray(): array
    {
        return [
            'ai_note' => $this->aiNote,
            'ai_risk_rating' => $this->aiRiskRating->value,
        ];
    }
}
