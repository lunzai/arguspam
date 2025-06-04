import type { BaseModel } from '$models/base-model.js';

export interface Org extends BaseModel {
	name: string;
	description: string;
	status: 'active' | 'inactive';
}

export interface CreateOrgRequest {
	name: string;
	description?: string;
	status: 'active' | 'inactive';
}

export interface UpdateOrgRequest {
	name?: string;
	description?: string;
	status?: 'active' | 'inactive';
}