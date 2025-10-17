<x-mail::message>
# Session Created - Monitoring Required

Hello {{ $notifiable->name }},

A session has been created for **{{ $session->asset->name }}** by **{{ $session->requester->name }}**.

## Session Details
- **Requester:** {{ $session->requester->name }} ({{ $session->requester->email }})
- **Asset:** {{ $session->asset->name }}
- **Scheduled Start:** {{ $session->scheduled_start_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Scheduled End:** {{ $session->scheduled_end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Requested Duration:** {{ $session->requestedDurationForHumans }}
- **Status:** {{ ucwords($session->status->value) }}

## Request Context
- **Original Reason:** {!! nl2br($session->request->reason) !!}
@if($session->request->intended_query)
- **Intended Query:** {!! nl2br($session->request->intended_query) !!}
@endif
@if($session->request->is_access_sensitive_data)
- **⚠️ Sensitive Data Access:** Yes
@if($session->request->sensitive_data_note)
- **Sensitive Data Note:** {!! nl2br($session->request->sensitive_data_note) !!}
@endif
@endif

## Your Monitoring Responsibilities

### Session Lifecycle Monitoring
📋 **You will be notified of:**
- When the requester starts the session
- When the session ends (manually or automatically)
- If the session is terminated (manually or automatically)
- If the session expires without being started
- If the session is cancelled by the requester

### AI Review & Risk Assessment
🔍 **After session completion:**
- AI will analyze all SQL queries and activities
- Risk rating will be assigned (Low, Medium, High, Critical)
- You will receive notifications based on risk level:
  - **Low-Medium Risk:** Optional manual review
  - **High-Critical Risk:** Mandatory manual audit required

### Manual Review Process
📝 **When manual review is required:**
- Review AI analysis and flagged activities
- Examine SQL query logs against stated purpose
- Assess policy compliance and data access patterns
- Take appropriate action based on findings

### Emergency Actions
🚨 **You can terminate the session if:**
- Security concerns arise during monitoring
- Policy violations are detected
- Unauthorized access patterns are observed
- Emergency access revocation is required

## Next Steps
The requester will be notified to start their session during the scheduled time period. You will receive additional notifications as the session progresses.

<x-mail::button :url="$url">
Monitor Session
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
