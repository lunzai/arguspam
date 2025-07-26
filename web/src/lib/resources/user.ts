import type { User } from '$models/user';
import type {
	ApiResourceResponse,
	ApiCollectionResponse,
	Resource,
	Collection
} from '$resources/api';

export interface ApiUserCollection extends ApiCollectionResponse<User> {}
export interface ApiUserResource extends ApiResourceResponse<User> {}
export interface UserResource extends Resource<User> {}
export interface UserCollection extends Collection<User> {}

export interface TwoFactorQrCodeResponse {
	data: {
		qr_code: string;
	};
}
