import type { BaseModel } from '$models/base-model.js';

export interface AssetAccessGrant extends BaseModel {
	asset_id: number;
	user_id: number;
	user_group_id: number;
	role: 'requester' | 'approver' | 'auditor';
}
