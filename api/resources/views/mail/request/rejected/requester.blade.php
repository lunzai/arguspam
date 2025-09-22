<x-mail::message>
# Request Rejected

Hello {{ $notifiable->name }},

We regret to inform you that your access request for **{{ $request->asset->name }}** has been rejected.

## Rejection Details
- **Rejected by:** {{ optional($request->rejecter)->name ?? 'System Administrator' }}
- **Rejected on:** {{ $request->rejected_at->format('M d, Y H:i') }}
@if($request->approver_risk_rating)
- **Risk Rating:** {{ $request->approver_risk_rating->value }}
@endif
@if($request->approver_note)

## Reason for Rejection
{{ $request->approver_note }}
@endif

## Request Summary
- **Asset:** {{ $request->asset->name }}
- **Requested Access Period:** {{ $request->start_datetime->format('M d, Y H:i') }} - {{ $request->end_datetime->format('M d, Y H:i') }}
- **Duration:** {{ $request->duration }}
- **Your Reason:** {{ $request->reason }}

## Next Steps
If you believe this rejection was made in error or if you need to discuss the decision further, please:

1. **Contact the rejecting approver** to understand the specific concerns
2. **Review your access requirements** and consider alternative approaches
3. **Submit a new request** with additional justification if appropriate
4. **Escalate to management** if the access is critical for business operations

For urgent access needs, please contact your line manager or the IT helpdesk.

<x-mail::button :url="$url">
View Request Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
