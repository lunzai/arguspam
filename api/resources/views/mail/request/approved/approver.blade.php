<x-mail::message>
# Request Approved - Notification

Hello {{ $notifiable->name }},

The access request for **{{ $request->asset->name }}** has been approved.

## Approval Details
- **Approved by:** {{ $request->approver->name }}
- **Approved on:** {{ $request->approved_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
@if($request->ai_risk_rating)
- **AI Risk Rating:** {{ ucwords($request->ai_risk_rating->value) }}
@endif
@if($request->approver_risk_rating)
- **Risk Rating:** {{ ucwords($request->approver_risk_rating->value) }}
@endif
@if($request->approver_note)
- **Approver Notes:** {!! nl2br($request->approver_note) !!}
@endif

## Request Summary
- **Asset:** {{ $request->asset->name }}
- **Access Period:** {{ $request->start_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} - {{ $request->end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }}  ({{ $notifiable->timezone }})
- **Duration:** {{ $request->durationForHumans }}
- **Reason:** {!! nl2br($request->reason) !!}

## Session Management & Monitoring
The requester has been notified and instructed to:
- Start their session through the **Sessions** section during the approved time period
- Terminate their session immediately upon completion
- Sessions will auto-terminate at the end of the approved period

## Audit & Compliance
📋 **Automatic Monitoring:**
- All session activities and queries will be recorded and audited
- Policy violations will be automatically flagged
- You will be notified of any compliance issues or violations
- Complete audit trails are maintained for security reviews

@if($request->is_access_sensitive_data)
## ⚠️ Sensitive Data Access
This approval grants access to sensitive data. Enhanced monitoring includes:
- Real-time session monitoring for sensitive data access
- Immediate flagging of unauthorized data queries
- Detailed audit logs for compliance reviews
- Automatic alerts for any policy violations
@endif

The requester has been notified of the approval and provided with detailed session management instructions.

<x-mail::button :url="$url">
View Request Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
