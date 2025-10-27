import type { BaseModel } from '$models/base-model.js';

export interface SessionAudit extends BaseModel {
	org_id: number;
	session_id: number;
	asset_id: number;
	user_id: number;
	username: string;
	query: string;
	command_type: string;
	count: number;
	first_timestamp: Date;
	last_timestamp: Date;
}
