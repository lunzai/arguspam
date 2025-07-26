import type { ActionAudit } from '$models/action-audit';
import type {
	ApiResourceResponse,
	ApiCollectionResponse,
	Resource,
	Collection
} from '$resources/api';

export interface ApiActionAuditCollection extends ApiCollectionResponse<ActionAudit> {}
export interface ApiActionAuditResource extends ApiResourceResponse<ActionAudit> {}
export interface ActionAuditResource extends Resource<ActionAudit> {}
export interface ActionAuditCollection extends Collection<ActionAudit> {}
