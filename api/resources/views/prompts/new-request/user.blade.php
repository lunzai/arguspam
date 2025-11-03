@php
    use App\Enums\RiskRating;
@endphp

**Analysis Requirements:**
- Provide a concise **ai_note** (50-200 words) with natural, flowing analysis that covers context evaluation, query review, and conclusion in a conversational manner.  
- Convert all durations from minutes into human-readable units (e.g., "120 minutes → 2 hours)", "10080 minutes → 7 days").  
- Highlight security, privacy, compliance, and operational risks.  
- Reference relevant compliance frameworks (GDPR, PCI-DSS, HIPAA, SOX, SOC2) when applicable.  
- If the query or reason is vague, assume the worst-case scenario.
- Maintain a professional, business-oriented tone (no first-person pronouns).

**Risk Rating:**
- Output **ai_risk_rating** as one of: {!! RiskRating::toString(', ') !!}, applying system guidelines and escalation rules.

**Output Format (MANDATORY):**
- Respond with **valid JSON only** — no text outside the JSON object.  
- JSON must be pretty-printed with `\n` line breaks for readability.  
- Use this schema exactly:  
{
  "ai_note": "<natural, flowing analysis with human-readable durations and final recommendation. Use line breaks to improve readability.>",
  "ai_risk_rating": "<{!! RiskRating::toString('|') !!}>"
}

Evaluate the following database access request:

**Request Details:**
- Database name: {!! $request->asset->name !!}
- Start datetime: {!! $request->start_datetime->format('c') !!}
- End datetime: {!! $request->end_datetime->format('c') !!}
- Duration (minutes): {!! $request->duration !!}
- Reason for access: {!! $request->reason !!}
- Intended query: {!! $request->intended_query !!}
- Access scope: {!! $request->scope->value !!}
- Is accessing sensitive data: {!! $request->is_access_sensitive_data ? 'Yes' : 'No' !!}
- Sensitive data note: {!! $request->sensitive_data_note !!}
