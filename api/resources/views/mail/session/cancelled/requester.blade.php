<x-mail::message>
# Session Cancelled

Hello {{ $notifiable->name }},

Your session for **{{ $session->asset->name }}** has been cancelled.

## Session Details
- **Asset:** {{ $session->asset->name }}
- **Scheduled Start:** {{ $session->scheduled_start_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Scheduled End:** {{ $session->scheduled_end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Cancelled at:** {{ $session->cancelled_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Status:** {{ ucwords($session->status->value) }}

## What This Means
- This session was cancelled before it was started
- No JIT credentials were created
- No access was granted to {{ $session->asset->name }}
- The original approved request remains in your records

## Need Access Again?
If you still need access to this asset, you will need to submit a new access request following the standard approval process.

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
