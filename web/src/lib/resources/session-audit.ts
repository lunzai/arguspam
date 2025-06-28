import type { SessionAudit } from '$models/session-audit';
import type { ApiResourceResponse, ApiCollectionResponse } from '$resources/api';

export interface SessionAuditCollection extends ApiCollectionResponse<SessionAudit> {}
export interface SessionAuditResource extends ApiResourceResponse<SessionAudit> {} 