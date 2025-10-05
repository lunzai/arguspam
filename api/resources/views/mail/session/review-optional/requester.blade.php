<x-mail::message>
# Session Review Recommended

Hello {{ $notifiable->name }},

Your session for **{{ $session->asset->name }}** has been reviewed and some minor anomalies were detected.

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
## Minor Issues Detected
@foreach($session->flags as $flag)
- **{{ ucwords(str_replace('_', ' ', $flag->flag->value)) }}**@if($flag->details): {{ $flag->details }}@endif
@endforeach
@endif

## What This Means
- AI has detected minor anomalies or medium-risk activities
- The approver has been notified and may optionally review your session
- While not critical, these flags indicate areas for improvement

## Recommendations
- Review the flagged activities to understand what triggered the alert
- Ensure future sessions more closely align with stated purposes
- Follow security policies more strictly to avoid flags

## What Happens Next
- The approver may choose to review your session in more detail
- If the approver decides no action is needed, the session will be closed
- You may be contacted if clarification is required

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
