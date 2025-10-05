<x-mail::message>
# Session Cancelled

Hello {{ $notifiable->name }},

A scheduled session has been cancelled by {{ $session->requester->name }}.

## Session Details
- **Requester:** {{ $session->requester->name }} ({{ $session->requester->email }})
- **Asset:** {{ $session->asset->name }}
- **Scheduled Start:** {{ $session->scheduled_start_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Scheduled End:** {{ $session->scheduled_end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Cancelled at:** {{ $session->cancelled_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Status:** {{ ucwords($session->status->value) }}

## Summary
- The session was cancelled before it started
- No credentials were created
- No access was granted
- The requester may submit a new request if access is still needed

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
