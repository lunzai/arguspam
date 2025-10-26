<x-mail::message>
# Session Started

Hello {{ $notifiable->name }},

Your session for **{{ $session->asset->name }}** has been started successfully.

## Session Details
- **Asset:** {{ $session->asset->name }}
- **Started at:** {{ $session->started_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Scheduled End:** {{ $session->scheduled_end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Requested Duration:** {{ $session->requestedDurationForHumans }}
- **Account Name:** {{ $session->account_name }}
- **Status:** {{ ucwords($session->status->value) }}

## Important Reminders

### During Your Session
- ✅ **Follow all security policies and procedures**
- ✅ **Only access data necessary for your stated purpose**
- ✅ **Complete your work within the approved timeframe**
- ✅ **All queries and activities are being recorded for audit**

### End Your Session
🔴 **CRITICAL: End your session when you're done**
- Click **End Session** as soon as you complete your work
- **Auto-termination:** Session will automatically end at {{ $session->scheduled_end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }}
- Your JIT credentials will be revoked when the session ends

### Post-Session Review
📋 **AI Review Process:**
- Your SQL queries will be collected after session ends
- AI will review activities against your stated purpose
- Any anomalies or policy violations will be flagged
- High-risk activities may trigger manual audit by approver

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
