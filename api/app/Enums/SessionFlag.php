<?php

namespace App\Enums;

use App\Traits\EnumToString;

enum SessionFlag: string
{
    use EnumToString;
    // Unauthorized access, privilege escalation, suspicious patterns, external data transfers
    case SECURITY_VIOLATION = 'SECURITY VIOLATION';
    // GDPR, regulatory, policy violations, audit tampering, data retention breaches
    case COMPLIANCE_VIOLATION = 'COMPLIANCE VIOLATION';
    // Excessive data access, PII exposure, bulk extraction, cross-boundary access
    case DATA_MISUSE = 'DATA MISUSE';
    // Unusual patterns, off-hours activity, geographic/device anomalies, rapid queries
    case ANOMALOUS_BEHAVIOR = 'ANOMALOUS BEHAVIOR';
    // Schema modifications, backdoor attempts, resource abuse, injection attacks
    case SYSTEM_INTEGRITY_RISK = 'SYSTEM INTEGRITY RISK';
}
