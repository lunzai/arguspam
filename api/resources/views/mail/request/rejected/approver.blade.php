<x-mail::message>
# Request Rejected - Notification

Hello {{ $notifiable->name }},

The access request for **{{ $request->asset->name }}** has been rejected.

## Rejection Summary
- **Requester:** {{ $request->requester->name }} ({{ $request->requester->email }})
- **Asset:** {{ $request->asset->name }}
- **Rejected on:** {{ $request->rejected_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Rejected by:** {{ optional($request->rejecter)->name ?? 'System Administrator' }}

## Request Details
- **Requested Access Period:** {{ $request->start_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} - {{ $request->end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Duration:** {{ $request->duration }}
- **Requester's Reason:** {{ $request->reason }}
@if($request->approver_risk_rating)
- **Rejection Risk Assessment:** {{ $request->approver_risk_rating->value }}
@endif
@if($request->approver_note)
- **Rejection Reason:** {{ $request->approver_note }}
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
