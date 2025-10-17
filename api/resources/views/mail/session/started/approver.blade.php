<x-mail::message>
# Session Started

Hello {{ $notifiable->name }},

A session you approved has been started by {{ $session->requester->name }}.

## Session Details
- **Requester:** {{ $session->requester->name }} ({{ $session->requester->email }})
- **Asset:** {{ $session->asset->name }}
- **Started at:** {{ $session->started_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Scheduled End:** {{ $session->scheduled_end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Requested Duration:** {{ $session->requestedDurationForHumans }}
- **Account Name:** {{ $session->account_name }}
- **Status:** {{ ucwords($session->status->value) }}

## Approver Actions

### Monitor the Session
- You can view real-time session status in the application
- All activities are being recorded for post-session audit

### Terminate if Needed
⚠️ **You can terminate this session at any time if:**
- You observe suspicious activity
- Security policy violations are detected
- Emergency access revocation is required

### Post-Session Review
- AI will automatically review all session activities
- You will be notified if manual audit is required
- High-risk sessions will be flagged for your review

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
