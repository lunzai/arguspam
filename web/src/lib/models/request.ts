import type { BaseModel } from '$models/base-model.js';

export interface Request extends BaseModel {
	org_id: number;
	asset_id: number;
	asset_account_id: number;
	requester_id: number;
	start_datetime: Date;
	end_datetime: Date;
	duration: number;
	reason: string;
	intended_query: string;
	scope: 'ReadOnly' | 'ReadWrite' | 'DDL' | 'DML' | 'All';
	is_access_sensitive_data: boolean;
	sensitive_data_note: string;
	approver_note: string;
	approver_risk_rating: 'low' | 'medium' | 'high' | 'critical';
	ai_note: string;
	ai_risk_rating: 'low' | 'medium' | 'high' | 'critical';
	status: 'pending' | 'approved' | 'rejected' | 'expired' | 'cancelled' | 'submitted';
	submitted_at: Date;
	approved_by: number;
	approved_at: Date;
	rejected_by: number;
	rejected_at: Date;
	cancelled_at: Date;
	cancelled_by: number;
	expired_at: Date;
}
