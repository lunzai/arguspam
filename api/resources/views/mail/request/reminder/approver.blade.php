<x-mail::message>
# 🔔 Urgent: Access Request Awaiting Your Review

Hello {{ $notifiable->name }},

**REMINDER:** You have a pending access request that requires immediate attention to avoid automatic expiration.

## ⏰ Time-Sensitive Request
- **Requester:** {{ $request->requester->name }} ({{ $request->requester->email }})
- **Asset:** {{ $request->asset->name }}
- **Requested Start:** {{ $request->start_datetime->format('M d, Y H:i') }}
- **Requested End:** {{ $request->end_datetime->format('M d, Y H:i') }}
- **Duration:** {{ $request->duration }}

## ⚠️ URGENT ACTION REQUIRED
**This request will automatically EXPIRE in {{ \Carbon\Carbon::now()->diffForHumans($request->start_datetime, true) }}**

If no approval or rejection action is taken by **{{ $request->start_datetime->format('M d, Y H:i') }}**, the system will automatically mark this request as expired for security compliance.

## Request Details
- **Business Justification:** {{ $request->reason }}
@if($request->intended_query)
- **Intended Query:** {{ $request->intended_query }}
@endif
@if($request->scope)
- **Scope:** {{ $request->scope->value }}
@endif
@if($request->is_access_sensitive_data)
- **⚠️ Involves Sensitive Data:** Yes
@if($request->sensitive_data_note)
- **Sensitive Data Details:** {{ $request->sensitive_data_note }}
@endif
@endif

## Required Action
Please review this request immediately and take one of the following actions:

### ✅ **APPROVE** if:
- The business justification is valid and appropriate
- The requested access scope is reasonable
- The requester has legitimate need for the resource
- All security requirements can be met

### ❌ **REJECT** if:
- Insufficient business justification provided
- Access scope is too broad or inappropriate  
- Security concerns cannot be adequately addressed
- Alternative solutions should be considered

@if($request->is_access_sensitive_data)
## 🔒 Sensitive Data Review Guidelines
This request involves access to sensitive data. Please pay special attention to:
- **Data Classification:** Ensure appropriate access level for the data type
- **Need-to-Know Basis:** Verify legitimate business requirement
- **Risk Assessment:** Consider potential security implications
- **Compliance Requirements:** Ensure all regulatory requirements are met
@endif

## Consequences of Inaction
If this request expires due to lack of review:
- The requester will be notified of the expiration
- A new request will need to be submitted if access is still required
- This may cause business delays and impact productivity
- Audit logs will record the expiration for compliance tracking

<x-mail::button :url="$url">
🚀 REVIEW & APPROVE/REJECT NOW
</x-mail::button>

**Need Help?** Contact the IT Security team if you have questions about this request or the approval process.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>