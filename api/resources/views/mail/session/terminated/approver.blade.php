<x-mail::message>
# Session Terminated

Hello {{ $notifiable->name }},

A session has been terminated.

## Session Details
- **Requester:** {{ $session->requester->name }} ({{ $session->requester->email }})
- **Asset:** {{ $session->asset->name }}
- **Started at:** {{ $session->started_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Terminated at:** {{ $session->terminated_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
@if($session->terminated_by)
- **Terminated by:** {{ $session->terminatedBy->name }}
@endif
- **Actual Duration:** {{ $session->actualDurationForHumans }}
- **Status:** {{ ucwords($session->status->value) }}

## Termination Reasons
This session may have been terminated due to:
- **Manual termination:** You or another approver terminated the session
- **Automatic timeout:** Session exceeded scheduled end time without manual end by requester
- **Security incident:** Emergency access revocation triggered

## Actions Taken
✅ JIT credentials have been immediately revoked
✅ Requester no longer has access to the asset
✅ All session activities have been logged

## Post-Termination Processing
The system will now:
1. Collect all SQL query logs from the session
2. Perform AI-based activity review
3. Generate risk assessment report
4. Flag any policy violations

### You Will Be Notified If:
⚠️ **Manual audit is required** - High risk rating or serious violations detected
💡 **Optional review recommended** - Medium risk or minor anomalies found

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
