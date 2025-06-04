import type { BaseModel } from './base-model.js';

export interface Org extends BaseModel {
	name: string;
	description: string;
	status: 'active' | 'inactive';
}

export interface CreateOrgRequest {
	name: string;
}

export interface UpdateOrgRequest {
	name?: string;
	description?: string;
	status?: 'active' | 'inactive';
} 