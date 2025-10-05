<x-mail::message>
# Request Submitted Successfully

Hello {{ $notifiable->name }},

Your access request for **{{ $request->asset->name }}** has been successfully submitted and is now pending approval.

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

## What's Next?
Your request has been forwarded to the appropriate approvers for review. You will receive an email notification once your request has been approved or if any additional information is required.

**Please note:** This request will expire if not approved within the specified timeframe. If urgent, please contact your system administrator.

<x-mail::button :url="$url">
View Request Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
