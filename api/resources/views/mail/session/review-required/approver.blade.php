<x-mail::message>
# 🚨 Manual Session Review Required

Hello {{ $notifiable->name }},

A session requires your immediate attention for manual audit.

## Session Summary
- **Requester:** {{ $session->requester->name }} ({{ $session->requester->email }})
- **Asset:** {{ $session->asset->name }}
- **Started at:** {{ $session->started_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Ended at:** {{ $session->ended_at ? $session->ended_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') : ($session->terminated_at ? $session->terminated_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') : 'N/A') }} ({{ $notifiable->timezone }})
- **Status:** {{ ucwords($session->status->value) }}

## AI Review Results
@if($session->ai_risk_rating)
- **AI Risk Rating:** {{ ucwords($session->ai_risk_rating->value) }}
@endif
@if($session->ai_reviewed_at)
- **Reviewed at:** {{ $session->ai_reviewed_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
@endif

@if($session->ai_note)
<x-mail::panel>
**AI Analysis:**

{!! nl2br($session->ai_note) !!}
</x-mail::panel>
@endif

@if($session->flags && $session->flags->count() > 0)
## 🚩 Flags Detected
The AI has identified the following issues:
@foreach($session->flags as $flag)
- **{{ ucwords(str_replace('_', ' ', $flag->flag->value)) }}**@if($flag->details): {{ $flag->details }}@endif
@endforeach
@endif

## Why Manual Review Is Required
Sessions are flagged for manual review when:
- **High risk rating** assigned by AI
- **Security policy violations** detected
- **Suspicious query patterns** identified
- **Unauthorized data access** suspected
- **Sensitive data exposure** risk present

## Your Action Required
Please review this session carefully:

1. **Review SQL Queries:** Examine all queries executed during the session
2. **Compare Against Purpose:** Verify activities align with the original request reason and intended queries
3. **Check for Violations:** Look for unauthorized access, data exfiltration, or policy breaches
4. **Document Findings:** Record your audit conclusions
5. **Take Action:** Clear the session or escalate as appropriate

## Access Session Audit
All SQL queries, timing information, and AI analysis are available in the session details.

<x-mail::button :url="$url">
Review Session Now
</x-mail::button>

**⏰ Please conduct this review promptly to ensure security compliance.**

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
