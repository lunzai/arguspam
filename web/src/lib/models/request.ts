import type { BaseModel } from '$models/base-model.js';

export interface Request extends BaseModel {
	id: number;
	org_id: number;
	asset_id: number;
	asset_account_id: number;
	requester_id: number;
	start_datetime: string;
	end_datetime: string;
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
	status: 'pending' | 'approved' | 'rejected' | 'expired';
	approved_by: number;
	approved_at: string;
	rejected_by: number;
	rejected_at: string;
    created_at: string;
	updated_at: string;
}