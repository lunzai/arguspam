import type { ActionAudit } from '$models/action-audit';
import type { ApiResourceResponse, ApiCollectionResponse } from '$resources/api';

export interface ActionAuditCollection extends ApiCollectionResponse<ActionAudit> {}
export interface ActionAuditResource extends ApiResourceResponse<ActionAudit> {} 