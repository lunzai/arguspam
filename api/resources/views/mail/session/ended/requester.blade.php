<x-mail::message>
# Session Ended

Hello {{ $notifiable->name }},

Your session for **{{ $session->asset->name }}** has been ended successfully.

## Session Summary
- **Asset:** {{ $session->asset->name }}
- **Started at:** {{ $session->started_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Ended at:** {{ $session->ended_at->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Actual Duration:** {{ $session->actualDurationForHumans }}
- **Account Name:** {{ $session->account_name }}
- **Status:** {{ ucwords($session->status->value) }}

## What Happens Next

### Credential Revocation
- ✅ Your JIT database credentials have been automatically revoked
- ✅ You no longer have access to {{ $session->asset->name }}

### Activity Review Process
📋 **Your session activities are being reviewed:**
1. **SQL query collection:** All queries executed during the session are being collected
2. **AI review:** AI will analyze your activities against your stated purpose
3. **Risk assessment:** Activities will be evaluated for policy compliance
4. **Flagging:** Any anomalies or violations will be flagged

### Possible Outcomes
- **Low risk, no flags:** Session review complete, no further action needed
- **Medium risk or minor flags:** Approver may be notified for optional manual review
- **High/Critical risk or serious violations:** Approver will be notified to conduct mandatory audit
- **High deviation risk:** Activities don't match stated purpose - may require review
- **High human audit confidence:** AI recommends human review for this session

You will be notified of the review results once the AI analysis is complete.

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
