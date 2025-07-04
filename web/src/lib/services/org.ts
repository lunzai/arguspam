import { BaseService } from './base.js';
import type { Org } from '$models/org';

export class OrgService extends BaseService<Org> {
	protected readonly endpoint = '/orgs';

	constructor(token: string, orgId?: number) {
		super('/orgs', token, orgId);
	}

	async getUsers(orgId: number) {
		return await this.api.get(`${this.endpoint}/${orgId}/users`);
	}
}
