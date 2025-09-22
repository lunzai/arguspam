<x-mail::message>
# Request Approved - Notification

Hello {{ $notifiable->name }},

The access request for **{{ $request->asset->name }}** has been approved.

## Approval Summary
- **Requester:** {{ $request->requester->name }} ({{ $request->requester->email }})
- **Asset:** {{ $request->asset->name }}
- **Approved on:** {{ $request->approved_at->format('M d, Y H:i') }}
- **Approved by:** {{ $request->approver->name }}

## Request Details
- **Access Period:** {{ $request->start_datetime->format('M d, Y H:i') }} - {{ $request->end_datetime->format('M d, Y H:i') }}
- **Duration:** {{ $request->duration }}
- **Reason:** {{ $request->reason }}
@if($request->approver_risk_rating)
- **Approver's Risk Assessment:** {{ $request->approver_risk_rating->value }}
@endif
@if($request->approver_note)
- **Approver's Notes:** {{ $request->approver_note }}
@endif

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
