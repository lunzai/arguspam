import type { BaseModel } from '$models/base-model.js';
import type { Role } from '$models/role';
import type { Permission } from '$models/permission';
import type { UserGroup } from '$models/user-group';
import type { Org } from '$models/org';

export interface User extends BaseModel {
	name: string;
	email: string;
	email_verified_at: Date | null;
	two_factor_enabled: boolean;
	two_factor_confirmed_at: Date | null;
	status: 'active' | 'inactive';
	default_timezone: string;
	last_login_at: Date | null;
}

export interface Me extends User {
	roles: Role[];
	permissions: Permission[];
	user_groups: UserGroup[];
	scheduled_sessions_count: number;
	submitted_requests_count: number;
	orgs: Org[];
}

export interface UserProfile {
	name: string;
	default_timezone: string;
}
