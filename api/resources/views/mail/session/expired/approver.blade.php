<x-mail::message>
# Session Expired

Hello {{ $notifiable->name }},

A scheduled session has expired without being started.

## Session Details
- **Requester:** {{ $session->requester->name }} ({{ $session->requester->email }})
- **Asset:** {{ $session->asset->name }}
- **Scheduled Start:** {{ $session->scheduled_start_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Scheduled End:** {{ $session->scheduled_end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Expired at:** {{ $session->expired_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Status:** {{ ucwords($session->status->value) }}

## Summary
- The requester did not start the session during the approved time window
- The scheduled end time has passed
- No credentials were created
- No access was granted
- The session has been automatically expired by the system

## What's Next?
- No action is required from you
- The requester may submit a new request if access is still needed
- The new request will go through the standard approval process

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
