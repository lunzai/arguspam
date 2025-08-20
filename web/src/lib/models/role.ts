import type { BaseModel } from '$models/base-model.js';

export interface Role extends BaseModel {
	name: string;
	description: string;
	is_default: boolean;
}
