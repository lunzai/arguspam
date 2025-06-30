import { BaseService } from './base.js';
import type { AssetAccessGrant } from '$models/asset-access-grant';

export class AssetAccessGrantService extends BaseService<AssetAccessGrant> {
	protected readonly endpoint = '/asset-access-grants';

	constructor(token: string, orgId?: number) {
		super('/asset-access-grants', token, orgId);
	}
}
