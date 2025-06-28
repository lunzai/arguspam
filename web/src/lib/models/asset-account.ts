import type { BaseModel } from '$models/base-model.js';

export interface AssetAccount extends BaseModel {
	id: number;
	asset_id: number;
	name: string;
    vault_path: string;
    is_default: boolean;
    created_at: string;
	updated_at: string;
}