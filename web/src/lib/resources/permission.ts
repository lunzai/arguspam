import type { Permission } from '$models/permission';
import type { ApiResourceResponse, ApiCollectionResponse } from '$resources/api';

export interface PermissionCollection extends ApiCollectionResponse<Permission> {}
export interface PermissionResource extends ApiResourceResponse<Permission> {} 