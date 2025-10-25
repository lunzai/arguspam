<x-mail::message>
# Session Ended

Hello {{ $notifiable->name }},

A session you approved has been ended by {{ $session->requester->name }}.

## Session Summary
- **Requester:** {{ $session->requester->name }} ({{ $session->requester->email }})
- **Asset:** {{ $session->asset->name }}
- **Started at:** {{ $session->started_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Ended at:** {{ $session->ended_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Actual Duration:** {{ $session->actualDurationForHumans }}
- **Status:** {{ ucwords($session->status->value) }}

## Post-Session Processing

### Automated Review
The system is now processing the session:
1. JIT credentials have been automatically revoked
2. SQL query logs are being collected
3. AI will review all activities against the request's stated purpose
4. Risk assessment and flagging will be performed

### You Will Be Notified If:
⚠️ **Manual audit is required** - High/Critical overall risk, high deviation risk, or serious policy violations detected
💡 **Optional review recommended** - Medium risk levels, medium human audit confidence, or minor anomalies detected

### Low Risk Sessions
✅ If no significant issues are found, the session will be automatically closed without requiring your review.

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
