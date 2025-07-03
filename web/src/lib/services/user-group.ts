import { BaseService } from './base.js';
import type { UserGroup } from '$models/user-group';

export class UserGroupService extends BaseService<UserGroup> {
	protected readonly endpoint = '/user-groups';

	constructor(token: string, orgId?: number) {
		super('/user-groups', token, orgId);
	}

	async addUsers(id: number, userIds: number[]) {
		return await this.api.post(`${this.endpoint}/${id}/users`, {
			user_ids: userIds
		});
	}
}
