import type { BaseModel } from '$models/base-model.js';

export interface Session extends BaseModel {
	org_id: number;
	request_id: number;
	asset_id: number;
	requester_id: number;
	start_datetime: Date;
	end_datetime: Date;
	scheduled_end_datetime: Date;
	requested_duration: number;
	actual_duration: number;
	is_jit: boolean;
	account_name: string;
	jit_vault_path: string;
	session_note: string;
	is_expired: boolean;
	is_terminated: boolean;
	is_checkin: boolean;
	status: 'scheduled' | 'active' | 'expired' | 'terminated' | 'ended';
	checkin_by: number;
	checkin_at: Date;
	terminated_by: number;
	terminated_at: Date;
	ended_at: Date;
	ended_by: number;
}
