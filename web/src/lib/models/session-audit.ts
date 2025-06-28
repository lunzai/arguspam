import type { BaseModel } from '$models/base-model.js';

export interface SessionAudit extends BaseModel {
	id: number;
	org_id: number;
	session_id: number;
	request_id: number;
	asset_id: number;
	user_id: number;
	query_text: string;
	query_timestamp: string;
	created_at: string;
}
