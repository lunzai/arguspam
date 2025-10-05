<x-mail::message>
# ⚠️ Session Terminated

Hello {{ $notifiable->name }},

Your session for **{{ $session->asset->name }}** has been terminated.

## Session Details
- **Asset:** {{ $session->asset->name }}
- **Started at:** {{ $session->started_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Terminated at:** {{ $session->terminated_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
@if($session->terminated_by)
- **Terminated by:** {{ $session->terminatedBy->name }}
@endif
- **Actual Duration:** {{ $session->actualDurationForHumans }}
- **Status:** {{ ucwords($session->status->value) }}

## What This Means
A session can be terminated for several reasons:
- **Manual termination by approver:** Security concerns or policy violations detected
- **Automatic termination:** Session exceeded the scheduled end time without being manually ended
- **Emergency access revocation:** Immediate security response required

## Immediate Actions Taken
✅ Your JIT database credentials have been immediately revoked
✅ You no longer have access to {{ $session->asset->name }}
✅ All session activities have been recorded for audit

## What Happens Next

### Activity Review
📋 Your session activities will be reviewed:
1. SQL query logs will be collected and analyzed
2. AI will evaluate activities against your stated purpose
3. Risk assessment will be performed
4. Any policy violations will be flagged

### Possible Follow-Up
- If terminated manually, the approver may contact you for clarification
- High-risk activities may trigger additional security reviews
- Policy violations may result in further action

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
