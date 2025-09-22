<x-mail::message>
# 🎉 Request Approved!

Hello {{ $notifiable->name }},

Great news! Your access request for **{{ $request->asset->name }}** has been approved.

## Approval Details
- **Approved by:** {{ $request->approver->name }}
- **Approved on:** {{ $request->approved_at->format('M d, Y H:i') }}
@if($request->approver_risk_rating)
- **Risk Rating:** {{ $request->approver_risk_rating->value }}
@endif
@if($request->approver_note)
- **Approver Notes:** {{ $request->approver_note }}
@endif

## Request Summary
- **Asset:** {{ $request->asset->name }}
- **Access Period:** {{ $request->start_datetime->format('M d, Y H:i') }} - {{ $request->end_datetime->format('M d, Y H:i') }}
- **Duration:** {{ $request->duration }}
- **Reason:** {{ $request->reason }}

## How to Access Your Approved Resource

### Step 1: Start Your Session
- Navigate to the **Sessions** section in the application
- Find your approved request during the approved time period
- Click **Start Session** to begin accessing the asset
- You will then have access to view and use the asset's access details

### Step 2: During Your Session
✅ **Follow all security policies and procedures**  
✅ **Only access data necessary for your stated purpose**  
✅ **Complete your work within the approved timeframe**  

### Step 3: End Your Session
🔴 **IMPORTANT: Terminate your session as soon as you're done**
- Click **End Session** when you've completed your work
- **Auto-termination:** Your session will automatically end at the approved time period's conclusion
- Do not leave sessions running unnecessarily

### Step 4: Post-Session Audit
📋 **Compliance Notice:** 
- All queries and activities performed during your session are recorded and audited
- Any policy violations will be automatically flagged
- Violations will be reported to relevant approvers and auditors
- Ensure all your activities comply with organizational policies

@if($request->is_access_sensitive_data)
**⚠️ Critical:** This request involves access to sensitive data. Exercise maximum caution and strictly follow all data protection guidelines. All sensitive data access is closely monitored.
@endif

<x-mail::button :url="$url">
View Request Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
