Evaluate the following database access request:

**Request Details:**
- Database name: {{database_name}}
- Start datetime: {{start_datetime}}
- End datetime: {{end_datetime}}
- Duration (minutes): {{duration}}
- Reason for access: {{reason}}
- Intended query: {{intended_query}}
- Access scope: {{access_scope}}
- Is accessing sensitive data: {{is_sensitive_data}}
- Sensitive data note: {{sensitive_data_note}}

**Analysis Requirements:**

1. **AI_note** (150–350 words, professional tone):
   - Address both technical and non-technical approvers
   - Analyze security, privacy, and compliance risks
   - Evaluate database context (production vs. non-production environments)
   - Assess query appropriateness and potential impact
   - **Duration Analysis:**
     • <={{low_threshold}} minutes: Note JIT alignment, minimal controls needed
     • {{low_threshold}}-{{medium_threshold}} minutes: Assess if duration can be reduced, require stronger justification
     • {{medium_threshold}}-{{high_threshold}} minutes: Request detailed daily breakdown, recommend intermediate check-ins  
     • {{high_threshold}}-{{max}} minutes: Challenge necessity, mandate enhanced monitoring, suggest multiple shorter requests
   - Reference relevant compliance frameworks (GDPR, PCI-DSS, HIPAA, SOX, SOC2)
   - If intended query or reason is vague, assume worst-case scenario within given scope
   - Always suggest breaking extended requests into JIT-appropriate chunks

2. **AI_risk_rating**:
   - Apply the risk rating guidelines from the system context
   - Consider all cumulative risk factors
   - Escalate risk level based on the defined rules

Provide analysis that enables quick, informed decision-making for human approvers while maintaining focus on security risk identification and policy compliance.