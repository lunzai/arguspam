import type { BaseModel } from '$models/base-model.js';

export interface User extends BaseModel {
	name: string;
	email: string;
	email_verified_at: Date | null;
	two_factor_enabled: boolean;
	two_factor_confirmed_at: Date | null;
	status: 'active' | 'inactive';
	last_login_at: Date | null;
}
