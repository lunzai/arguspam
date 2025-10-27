import type { BaseModel } from '$models/base-model.js';

export interface SessionFlag extends BaseModel {
	org_id: number;
	session_id: number;
	flag: string;
	created_at: Date;
}
