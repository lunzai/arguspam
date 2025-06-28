import { BaseService } from './base.js';
import type { Role } from '$models/role';

export class RoleService extends BaseService<Role> {
	protected readonly endpoint = '/roles';

	constructor(token: string, orgId?: number) {
		super('/roles', token, orgId);
	}
} 