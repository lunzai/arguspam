import type { User } from '$models/user';
import type { ApiResourceResponse } from '$resources/api';

export interface UserResource extends ApiResourceResponse<User> {}
