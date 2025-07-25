import type { UserGroup } from '$models/user-group';
import type { ApiResourceResponse, ApiCollectionResponse, Resource, Collection } from '$resources/api';

export interface ApiUserGroupCollection extends ApiCollectionResponse<UserGroup> {}
export interface ApiUserGroupResource extends ApiResourceResponse<UserGroup> {}
export interface UserGroupResource extends Resource<UserGroup> {}
export interface UserGroupCollection extends Collection<UserGroup> {}
