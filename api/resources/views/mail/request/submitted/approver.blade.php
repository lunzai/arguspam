<x-mail::message>
# Access Request Awaiting Your Approval

Hello {{ $notifiable->name }},

A new access request has been submitted and requires your approval.

## Request Summary
- **Requester:** {{ $request->requester->name }} ({{ $request->requester->email }})
- **Asset:** {{ $request->asset->name }}
- **Access Period:** {{ $request->start_datetime->format('M d, Y H:i') }} - {{ $request->end_datetime->format('M d, Y H:i') }}
- **Duration:** {{ $request->duration }}
- **Reason:** {{ $request->reason }}

## Request Details
@if($request->intended_query)
- **Intended Query:** {{ $request->intended_query }}
@endif
@if($request->scope)
- **Scope:** {{ $request->scope->value }}
@endif
@if($request->is_access_sensitive_data)
- **⚠️ Accessing Sensitive Data:** Yes
@if($request->sensitive_data_note)
- **Sensitive Data Note:** {{ $request->sensitive_data_note }}
@endif
@endif

## Action Required
Please review this request and either approve or reject it. **This request will expire if not reviewed within the specified timeframe.**

When reviewing, please consider:
- The business justification provided
- The scope and duration of access requested
- Whether sensitive data access is appropriate
- The requester's role and responsibilities

<x-mail::button :url="$url">
Review & Approve/Reject Request
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
