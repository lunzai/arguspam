import { BaseService } from './base.js';
import type { AssetAccount } from '$models/asset-account';

export class AssetAccountService extends BaseService<AssetAccount> {
	protected readonly endpoint = '/asset-accounts';

	constructor(token: string, orgId?: number) {
		super('/asset-accounts', token, orgId);
	}
} 