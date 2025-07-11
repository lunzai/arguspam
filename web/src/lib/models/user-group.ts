import type { BaseModel } from '$models/base-model.js';

export interface UserGroup extends BaseModel {
	id: number;
	org_id: number;
	name: string;
	description: string;
	status: 'active' | 'inactive';
	user_count?: number;
	created_at: string;
	updated_at: string;
}
