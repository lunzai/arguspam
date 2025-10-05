<x-mail::message>
# ⚠️ Session Review Required

Hello {{ $notifiable->name }},

Your session for **{{ $session->asset->name }}** has been flagged and requires manual review by an approver.

## Session Summary
- **Asset:** {{ $session->asset->name }}
- **Started at:** {{ $session->started_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Ended at:** {{ $session->ended_at ? $session->ended_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') : ($session->terminated_at ? $session->terminated_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') : 'N/A') }} ({{ $notifiable->timezone }})
- **Status:** {{ ucwords($session->status->value) }}

## AI Review Results
@if($session->ai_risk_rating)
- **AI Risk Rating:** {{ ucwords($session->ai_risk_rating->value) }}
@endif
@if($session->ai_note)
- **AI Note:** {!! nl2br($session->ai_note) !!}
@endif

@if($session->flags && $session->flags->count() > 0)
## Flags Detected
The following issues were identified during the AI review:
@foreach($session->flags as $flag)
- **{{ ucwords(str_replace('_', ' ', $flag->flag->value)) }}**@if($flag->details): {{ $flag->details }}@endif
@endforeach
@endif

## What This Means
- AI has detected potential policy violations or high-risk activities
- Your session activities are being reviewed by {{ $session->approver->name }}
- The approver will conduct a manual audit of your session

## What Happens Next
1. **Approver Review:** The approver will examine your session activities, SQL queries, and the AI analysis
2. **Possible Outcomes:**
   - **Cleared:** Activities are found to be compliant, no further action needed
   - **Warning:** Minor violations identified, you may receive guidance
   - **Violation:** Serious policy breach detected, may result in disciplinary action

## Your Responsibility
- Be prepared to provide clarification if requested by the approver
- Ensure all future sessions strictly follow security policies
- Review the flagged issues to understand what triggered the alert

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
