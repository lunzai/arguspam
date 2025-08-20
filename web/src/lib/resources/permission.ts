import type { Permission } from '$models/permission';
import type {
	ApiResourceResponse,
	ApiCollectionResponse,
	Resource,
	Collection
} from '$resources/api';

export interface ApiPermissionCollection extends ApiCollectionResponse<Permission> {}
export interface ApiPermissionResource extends ApiResourceResponse<Permission> {}
export interface PermissionResource extends Resource<Permission> {}
export interface PermissionCollection extends Collection<Permission> {}
