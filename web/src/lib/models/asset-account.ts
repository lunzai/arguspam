import type { BaseModel } from '$models/base-model.js';

export interface AssetAccount extends BaseModel {
	asset_id: number;
	username: string;
	password: string;
	type: 'admin' | 'jit';
	expires_at: Date | null;
	is_active: boolean;
}
