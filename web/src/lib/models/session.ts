import type { BaseModel } from '$models/base-model.js';

export interface Session extends BaseModel {
	org_id: number;
	request_id: number;
	asset_id: number;
	requester_id: number;
	approver_id: number;
	start_datetime: Date;
	end_datetime: Date;
	scheduled_start_datetime: Date;
	scheduled_end_datetime: Date;
	requested_duration: number;
	actual_duration: number;
	is_admin_account: boolean;
	account_name: string;
	session_activity_risk: 'low' | 'medium' | 'high' | 'critical';
	deviation_risk: 'low' | 'medium' | 'high' | 'critical';
	overall_risk: 'low' | 'medium' | 'high' | 'critical';
	human_audit_required: boolean;
	human_audit_confidence: number;
	ai_note: string;
	ai_reviewed_at: Date;
	session_note: string;
	status: 'scheduled' | 'started' | 'ended' | 'cancelled' | 'terminated' | 'expired';
	started_at: Date;
	started_by: number;
	ended_at: Date;
	ended_by: number;
	cancelled_at: Date;
	cancelled_by: number;
	terminated_at: Date;
	terminated_by: number;
	expired_at: Date;
	created_by: number;
	created_at: Date;
	updated_by: number;
	updated_at: Date;
}
