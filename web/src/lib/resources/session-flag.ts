import type { SessionFlag } from '$models/session-flag';
import type {
	ApiResourceResponse,
	ApiCollectionResponse,
	Resource,
	Collection
} from '$resources/api';

export interface ApiSessionFlagCollection extends ApiCollectionResponse<SessionFlag> {}
export interface ApiSessionFlagResource extends ApiResourceResponse<SessionFlag> {}
export interface SessionFlagResource extends Resource<SessionFlag> {}
export interface SessionFlagCollection extends Collection<SessionFlag> {}
