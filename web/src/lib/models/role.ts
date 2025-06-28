import type { BaseModel } from '$models/base-model.js';

export interface Role extends BaseModel {
	id: number;
	name: string;
	description: string;
	is_default: boolean;
	created_at: string;
	updated_at: string;
}