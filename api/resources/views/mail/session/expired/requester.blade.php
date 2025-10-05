<x-mail::message>
# Session Expired

Hello {{ $notifiable->name }},

Your session for **{{ $session->asset->name }}** has expired.

## Session Details
- **Asset:** {{ $session->asset->name }}
- **Scheduled Start:** {{ $session->scheduled_start_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Scheduled End:** {{ $session->scheduled_end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Expired at:** {{ $session->expired_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Status:** {{ ucwords($session->status->value) }}

## What This Means
- Your session was not started before the scheduled end time
- The approved access window has passed
- No JIT credentials were created
- No access was granted to {{ $session->asset->name }}

## Why Did This Happen?
Sessions expire when:
- You did not start the session during the approved time window
- The scheduled end datetime has passed
- The system automatically expired the unused session

## Need Access Again?
If you still need access to this asset, you must:
1. Submit a new access request
2. Get approval from an approver
3. Start the new session within the approved time window

**💡 Tip:** Make sure to start your session promptly after approval to avoid expiration.

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
