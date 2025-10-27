<?php

namespace App\Services\OpenAI\Responses;

use App\Enums\RiskRating;
use App\Enums\SessionFlag;

class SessionAudit extends BaseResponse
{
    public function __construct(
        public string $aiNote,
        public RiskRating $sessionActivityRisk,
        public RiskRating $deviationRisk,
        public RiskRating $overallRisk,
        public array $flags,
        public int $humanAuditConfidence,
        public bool $humanAuditRequired,
    ) {}

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: '.json_last_error_msg());
        }
        return new self(
            $data['ai_note'],
            RiskRating::from($data['session_activity_risk']),
            RiskRating::from($data['deviation_risk']),
            RiskRating::from($data['overall_risk']),
            array_map(fn ($flag) => SessionFlag::from($flag), $data['flags']),
            (int) $data['human_audit_confidence'],
            (bool) $data['human_audit_required'],
        );
    }

    public function toArray(): array
    {
        return [
            'ai_note' => $this->aiNote,
            'session_activity_risk' => $this->sessionActivityRisk->value,
            'deviation_risk' => $this->deviationRisk->value,
            'overall_risk' => $this->overallRisk->value,
            'flags' => $this->flags,
            'human_audit_confidence' => $this->humanAuditConfidence,
            'human_audit_required' => $this->humanAuditRequired,
        ];
    }
}
