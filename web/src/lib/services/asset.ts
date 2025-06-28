import { BaseService } from './base.js';
import type { Asset } from '$models/asset';

export class AssetService extends BaseService<Asset> {
	protected readonly endpoint = '/assets';

	constructor(token: string, orgId?: number) {
		super('/assets', token, orgId);
	}
} 