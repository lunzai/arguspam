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

	async addAccessGrant(
		id: number,
		role: string,
		userIds: string[] | number[],
		userGroupIds: string[] | number[]
	) {
		return await this.api.post(`${this.endpoint}/${id}/access-grant`, {
			role,
			user_ids: userIds,
			user_group_ids: userGroupIds
		});
	}

	async removeUserOrGroup(
		id: number,
		role: string,
		refId: number | string,
		type: 'user' | 'user_group'
	) {
		const payload = {
			role,
			type,
			user_id: type === 'user' ? refId : null,
			user_group_id: type === 'user_group' ? refId : null
		};
		return await this.api.delete(`${this.endpoint}/${id}/access-grant`, payload);
	}
}
