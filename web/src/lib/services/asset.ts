import { BaseService } from './base.js';
import type { Asset } from '$models/asset';

export class AssetService extends BaseService<Asset> {
	protected readonly endpoint = '/assets';

	constructor(token: string, orgId?: number) {
		super('/assets', token, orgId);
	}

	async testConnection(id: number) {
		return await this.api.get(`${this.endpoint}/${id}/connection`);
	}

	async updateCredentials(id: number, data: any) {
		return await this.api.put(`${this.endpoint}/${id}/credential`, data);
	}
}
