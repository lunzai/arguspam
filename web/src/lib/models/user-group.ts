import type { BaseModel } from '$models/base-model.js';

export interface UserGroup extends BaseModel {
	org_id: number;
	name: string;
	description: string;
	status: 'active' | 'inactive';
	user_count?: number;
}
