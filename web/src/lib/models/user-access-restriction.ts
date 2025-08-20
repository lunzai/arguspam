import type { BaseModel } from '$models/base-model.js';

export interface UserAccessRestriction extends BaseModel {
	user_id: number;
	type: 'ip_address' | 'time_window' | 'location' | 'device';
	value: string;
	status: 'active' | 'inactive';
}
