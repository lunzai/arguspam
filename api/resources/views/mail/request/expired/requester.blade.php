<x-mail::message>
# ⏰ Request Expired - No Action Taken

Hello {{ $notifiable->name }},

Your access request for **{{ $request->asset->name }}** has expired without approval or rejection.

## Expiration Details
- **Asset:** {{ $request->asset->name }}
- **Requested Access Period:** {{ $request->start_datetime->format('M d, Y H:i') }} - {{ $request->end_datetime->format('M d, Y H:i') }}
- **Expired on:** {{ $request->start_datetime->format('M d, Y H:i') }}
- **Duration:** {{ $request->duration }}
- **Your Reason:** {{ $request->reason }}

## What Happened?
Your request was not reviewed and approved/rejected by the scheduled start time. As per security policy, requests that are not processed within the designated timeframe are automatically marked as expired to maintain system security and compliance.

## Next Steps
If you still need access to this resource, you have the following options:

### 1. **Submit a New Request**
- Create a fresh access request with updated timeframes
- Consider providing additional justification or context
- Ensure sufficient lead time for the approval process

### 2. **Contact Approvers Directly**
- Reach out to the relevant approvers to discuss your access needs
- Explain the urgency if this is time-sensitive
- Ask about expedited review processes if available

### 3. **Escalate if Urgent**
- Contact your line manager if access is business-critical
- Involve the IT helpdesk for emergency access procedures
- Provide clear business justification for urgent processing

@if($request->is_access_sensitive_data)
## ⚠️ Sensitive Data Access Note
Since your original request involved access to sensitive data, please ensure that any new request includes proper justification and follows enhanced security protocols.
@endif

## Prevention Tips for Future Requests
- **Submit requests well in advance** of when you need access
- **Provide clear, detailed justification** for your access requirements
- **Follow up with approvers** if requests are pending close to start times
- **Consider business hours and holidays** when planning request timelines

<x-mail::button :url="$url">
View Expired Request Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>