import type { User } from '$models/user';
import type { ApiResourceResponse, ApiCollectionResponse } from '$resources/api';

export interface UserCollection extends ApiCollectionResponse<User> {}
export interface UserResource extends ApiResourceResponse<User> {}
