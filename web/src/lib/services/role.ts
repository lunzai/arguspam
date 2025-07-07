import { BaseService } from './base.js';
import type { Role } from '$models/role';

export class RoleService extends BaseService<Role> {
	protected readonly endpoint = '/roles';
	constructor(token: string, orgId?: number) {
		super('/roles', token, orgId);
	}

	async syncPermissions(id: number, permissionIds: number[]) {
		await this.api.put(`${this.endpoint}/${id}/permissions`, {
			permission_ids: permissionIds
		});
		return {
			type: 'success',
			message: 'Permissions synced successfully'
		};
	}

	async getPermissions(id: number) {
		return await this.api.get(`${this.endpoint}/${id}/permissions`);
	}
}
