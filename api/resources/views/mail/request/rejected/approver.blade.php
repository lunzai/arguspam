<x-mail::message>
# Request Rejected - Notification

Hello {{ $notifiable->name }},

The access request for **{{ $request->asset->name }}** has been rejected.

## Request Summary
- **Asset:** {{ $request->asset->name }}
- **Requested Access Period:** {{ $request->start_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} - {{ $request->end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Duration:** {{ $request->durationForHumans }}
- **Your Reason:** {!! nl2br($request->reason) !!}

## Rejection Details
- **Rejected by:** {{ optional($request->rejecter)->name ?? 'System Administrator' }}
- **Rejected on:** {{ $request->rejected_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
@if($request->ai_risk_rating)
- **AI Risk Rating:** {{ ucwords($request->ai_risk_rating->value) }}
@endif
@if($request->approver_risk_rating)
- **Risk Rating:** {{ ucwords($request->approver_risk_rating->value) }}
@endif
@if($request->approver_note)

## Reason for Rejection
{!! nl2br($request->approver_note) !!}
@endif

## Follow-up Actions
The requester has been notified of the rejection and provided with guidance on next steps. They may:

- Contact the rejecting approver directly to discuss the decision
- Submit a new request with additional justification
- Escalate the matter through management channels

As an approver, you may be contacted if the requester needs clarification or wishes to discuss alternative approaches.

<x-mail::button :url="$url">
View Request Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
