import type { BaseModel } from '$models/base-model.js';

export interface Asset extends BaseModel {
	id: number;
	org_id: number;
	name: string;
	description: string;
	status: 'active' | 'inactive';
	host: string;
	port: number;
	dbms: string;
	created_at: string;
	updated_at: string;
}
