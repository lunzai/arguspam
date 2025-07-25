import type { BaseModel } from '$models/base-model.js';

export interface SessionAudit extends BaseModel {
	org_id: number;
	session_id: number;
	request_id: number;
	asset_id: number;
	user_id: number;
	query_text: string;
	query_timestamp: Date;
}
