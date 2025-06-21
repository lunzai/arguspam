import type { BaseModel } from '$models/base-model.js';

export interface Org extends BaseModel {
	id: number;
	name: string;
	description: string;
	status: 'active' | 'inactive';
	created_at: string;
	updated_at: string;
}


// export interface CreateOrgRequest {
// 	name: string;
// 	description?: string;
// 	status: 'active' | 'inactive';
// }

// export interface UpdateOrgRequest {
// 	name?: string;
// 	description?: string;
// 	status?: 'active' | 'inactive';
// }
