<?php

namespace App\Enums;

enum AiAgentRole: string
{
    case AccessRequestEvaluation = 'access_request_evaluation';
    case SessionReview = 'session_review';
}
