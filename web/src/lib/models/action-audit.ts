import type { BaseModel } from '$models/base-model.js';

export interface ActionAudit extends BaseModel {
	id: number;
	org_id: number;
	user_id: number;
	action_type: string;
	entity_type: string;
	entity_id: number;
	description: string;
	previous_state: string;
	new_state: string;
	ip_address: string;
	user_agent: string;
	additional_data: string;
	created_at: string;
}
