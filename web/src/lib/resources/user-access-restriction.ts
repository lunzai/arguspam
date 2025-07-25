import type { UserAccessRestriction } from '$models/user-access-restriction';
import type { ApiResourceResponse, ApiCollectionResponse } from '$resources/api';

export interface ApiUserAccessRestrictionCollection
	extends ApiCollectionResponse<UserAccessRestriction> {}
export interface ApiUserAccessRestrictionResource
	extends ApiResourceResponse<UserAccessRestriction> {}
export interface UserAccessRestrictionResource extends Resource<UserAccessRestriction> {}
export interface UserAccessRestrictionCollection extends Collection<UserAccessRestriction> {}
