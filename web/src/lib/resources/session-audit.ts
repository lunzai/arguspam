import type { SessionAudit } from '$models/session-audit';
import type { ApiResourceResponse, ApiCollectionResponse, Resource, Collection } from '$resources/api';

export interface ApiSessionAuditCollection extends ApiCollectionResponse<SessionAudit> {}
export interface ApiSessionAuditResource extends ApiResourceResponse<SessionAudit> {}
export interface SessionAuditResource extends Resource<SessionAudit> {}
export interface SessionAuditCollection extends Collection<SessionAudit> {}
