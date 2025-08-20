import type { Role } from '$models/role';
import type {
	ApiResourceResponse,
	ApiCollectionResponse,
	Resource,
	Collection
} from '$resources/api';

export interface ApiRoleCollection extends ApiCollectionResponse<Role> {}
export interface ApiRoleResource extends ApiResourceResponse<Role> {}
export interface RoleResource extends Resource<Role> {}
export interface RoleCollection extends Collection<Role> {}
