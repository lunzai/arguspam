import type { UserAccessRestriction } from '$models/user-access-restriction';
import type { ApiResourceResponse, ApiCollectionResponse } from '$resources/api';

export interface UserAccessRestrictionCollection
	extends ApiCollectionResponse<UserAccessRestriction> {}
export interface UserAccessRestrictionResource extends ApiResourceResponse<UserAccessRestriction> {}
