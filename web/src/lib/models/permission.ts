import type { BaseModel } from '$models/base-model.js';

export interface Permission extends BaseModel {
	id: number;
	name: string;
	description: string;
	created_at: string;
	updated_at: string;
}