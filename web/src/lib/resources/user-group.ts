import type { UserGroup } from '$models/user-group';
import type { ApiResourceResponse, ApiCollectionResponse } from '$resources/api';

export interface UserGroupCollection extends ApiCollectionResponse<UserGroup> {}
export interface UserGroupResource extends ApiResourceResponse<UserGroup> {} 