<x-mail::message>
# Access Request Awaiting Your Approval

Hello {{ $notifiable->name }},

A new access request has been submitted and requires your approval.

## Request Summary
- **Requester:** {{ $request->requester->name }} ({{ $request->requester->email }})
- **Asset:** {{ $request->asset->name }}
- **Access Period:** {{ $request->start_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} - {{ $request->end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Duration:** {{ $request->durationForHumans }}
- **Reason:** {!! nl2br($request->reason) !!}

## Request Details
@if($request->intended_query)
- **Intended Query:** {!! nl2br($request->intended_query) !!}
@endif
@if($request->scope)
- **Scope:** {{ ucwords($request->scope->value) }}
@endif
@if($request->is_access_sensitive_data)
- **⚠️ Accessing Sensitive Data:** Yes
@if($request->sensitive_data_note)
- **Sensitive Data Note:** {!! nl2br($request->sensitive_data_note) !!}
@endif
@endif
@if ($request->ai_note || $request->ai_risk_rating)
<x-mail::panel>
@if ($request->ai_risk_rating)
**AI Risk Rating:** {{ ucwords($request->ai_risk_rating->value) }}
@endif

@if ($request->ai_note)
**AI Note:**

{!! nl2br($request->ai_note) !!}
@endif
</x-mail::panel>
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
