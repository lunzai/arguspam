<x-mail::message>
# Request Cancelled

Hello {{ $notifiable->name }},

Your access request for **{{ $request->asset->name }}** has been cancelled.

## Request Summary
- **Asset:** {{ $request->asset->name }}
- **Requested Access Period:** {{ $request->start_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} - {{ $request->end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Duration:** {{ $request->durationForHumans }}
- **Your Reason:** {!! nl2br($request->reason) !!}

## Cancellation Details
- **Cancelled by:** {{ optional($request->canceller)->name ?? $notifiable->name }}
- **Cancelled on:** {{ $request->cancelled_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})

## What This Means
- **Access will not be granted** for the originally requested time period
- **No further action** is required from approvers for this request

## Next Steps
If you still need access to this asset, you may:

1. **Submit a new request** with updated timing or requirements
2. **Contact your line manager** if the access is still critical
3. **Review alternative solutions** that might meet your needs
4. **Contact the asset owner** to discuss alternative access methods

For urgent access needs, please contact your line manager or the IT helpdesk.

<x-mail::button :url="$url">
View Request Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>