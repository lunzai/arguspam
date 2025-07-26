import { BaseService } from './base.js';
import type { Org } from '$models/org';
import type { ApiUserCollection } from '$lib/resources/user';
import type { BaseFindByIdParams } from './base';

export class OrgService extends BaseService<Org> {
	protected readonly endpoint = '/orgs';

	constructor(token: string, orgId?: number) {
		super('/orgs', token, orgId);
	}

	async getUsers(orgId: number, params: BaseFindByIdParams = { perPage: 10000 }) {
        const queryString = this.buildQueryParams(params);
		const url = `${this.endpoint}/${orgId}/users?${queryString}`;
		return await this.api.get<ApiUserCollection>(url);
	}

	async deleteUser(id: number, userIds: string[] | number[]) {
		return await this.api.delete(`${this.endpoint}/${id}/users`, {
			user_ids: userIds
		});
	}

	async addUsers(id: number, userIds: string[] | number[]) {
		return await this.api.post(`${this.endpoint}/${id}/users`, {
			user_ids: userIds
		});
	}
}
