import type { Session } from '$models/session';
import type {
	ApiResourceResponse,
	ApiCollectionResponse,
	Resource,
	Collection,
	ApiResponse
} from '$resources/api';

export interface ApiSessionCollection extends ApiCollectionResponse<Session> {}
export interface ApiSessionResource extends ApiResourceResponse<Session> {}
export interface SessionResource extends Resource<Session> {}
export interface SessionCollection extends Collection<Session> {}

export interface SessionPermission {
	canTerminate: boolean;
	canCancel: boolean;
	canStart: boolean;
	canEnd: boolean;
	canRetrieveSecret: boolean;
}
export interface SessionPermissionResource extends ApiResponse<SessionPermission> {}
