<x-mail::message>
# Session Review Optional

Hello {{ $notifiable->name }},

A session has been flagged with minor anomalies. Manual review is optional.

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
## Minor Issues Detected
@foreach($session->flags as $flag)
- **{{ ucwords(str_replace('_', ' ', $flag->flag->value)) }}**@if($flag->details): {{ $flag->details }}@endif
@endforeach
@endif

## Review Recommendation
This session has been flagged with:
- Medium risk rating OR
- Minor policy anomalies OR
- Activities that slightly deviate from stated purpose

While not critical, you may want to review this session to:
- Ensure compliance with organizational policies
- Provide guidance to the requester
- Identify patterns that may need attention

## Your Options
✅ **Review Now:** Click the button below to examine the session details and SQL queries
⏭️ **Skip Review:** If you're confident this is acceptable, you can choose not to review
📋 **Review Later:** The session details remain available for future audit if needed

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

**Note:** This is an optional review. If no action is taken, the session will be considered reviewed and closed.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
