import type { BaseModel } from '$models/base-model.js';

export interface Permission extends BaseModel {
	name: string;
	description: string;
}
