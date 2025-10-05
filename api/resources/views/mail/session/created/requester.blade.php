<x-mail::message>
# Session Created Successfully

Hello {{ $notifiable->name }},

Your session for **{{ $session->asset->name }}** has been created and is ready to start.

## Session Summary
- **Requester:** {{ $session->requester->name }} ({{ $session->requester->email }})
- **Asset:** {{ $session->asset->name }}
- **Scheduled Start:** {{ $session->scheduled_start_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Scheduled End:** {{ $session->scheduled_end_datetime->setTimezone($notifiable->getTimezone())->format('M d, Y H:i') }} ({{ $notifiable->timezone }})
- **Duration:** {{ $session->scheduledDurationForHumans }}
- **Status:** {{ ucwords($session->status->value) }}

## How to Start Your Session

### Step 1: Start Your Session
- Navigate to the **Sessions** section in the application
- Find your scheduled session during the approved time period
- Click **Start Session** to begin accessing the asset
- JIT credentials will be automatically created for you

### Step 2: During Your Session
✅ **Follow all security policies and procedures**
✅ **Only access data necessary for your stated purpose**
✅ **Complete your work within the approved timeframe**

### Step 3: End Your Session
🔴 **IMPORTANT: End your session as soon as you're done**
- Click **End Session** when you've completed your work
- **Auto-termination:** Your session will automatically end at the scheduled end time
- Do not leave sessions running unnecessarily

### Step 4: Post-Session Audit
📋 **Compliance Notice:**
- All queries and activities performed during your session are recorded and audited
- AI will review your activities against your stated purpose
- Any policy violations will be automatically flagged
- Ensure all your activities comply with organizational policies

<x-mail::button :url="$url">
View Session Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
