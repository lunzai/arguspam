import type { BaseModel } from '$models/base-model.js';

export interface User extends BaseModel {
	name: string;
	email: string;
	email_verified_at: string | null;
	two_factor_enabled: boolean;
	two_factor_confirmed_at: string | null;
	status: 'active' | 'inactive';
	last_login_at: string | null;
	created_by: number | null;
	updated_by: number | null;
}

// TODO
// export interface CreateUserRequest {
// 	name: string;
// 	email: string;
// 	password: string;
// 	password_confirmation: string;
// }

// export interface UpdateUserRequest {
// 	name?: string;
// 	email?: string;
// 	status?: string;
// } 