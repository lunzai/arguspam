import type { Role } from '$models/role';
import type { ApiResourceResponse, ApiCollectionResponse } from '$resources/api';

export interface RoleCollection extends ApiCollectionResponse<Role> {}
export interface RoleResource extends ApiResourceResponse<Role> {}
