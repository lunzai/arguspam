@php
    use App\Enums\RequestStatus;
@endphp
<x-mail::message>
# Request Cancelled - Notification

Hello {{ $notifiable->name }},

@if($request->status == RequestStatus::SUBMITTED)
The access request for **{{ $request->asset->name }}** that was pending your approval has been cancelled.
@else
The access request for **{{ $request->asset->name }}** has been cancelled.
@endif

## Request Summary
- **Asset:** {{ $request->asset->name }}
- **Requester:** {{ $request->requester->name }}
- **Requested Access Period:** {{ $request->start_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} - {{ $request->end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Duration:** {{ $request->durationForHumans }}
- **Reason:** {!! nl2br($request->reason) !!}

## Cancellation Details
- **Cancelled by:** {{ optional($request->canceller)->name ?? $request->requester->name }}
- **Cancelled on:** {{ $request->cancelled_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})

## Impact on Your Workflow
- **No action required** from you as an approver
- **Request has been removed** from your pending approval queue
- **All approval workflows** for this request have been terminated
- **Requester has been notified** of the cancellation

## Follow-up Information
The requester may submit a new access request if their needs change. If they do, you will receive a new approval notification following the standard process.

If you have any questions about this cancellation or need to discuss the asset access requirements, please contact the requester directly.

<x-mail::button :url="$url">
View Request Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>