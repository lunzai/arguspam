import { BaseService } from './base.js';
import type { Permission } from '$models/permission';

export class PermissionService extends BaseService<Permission> {
	protected readonly endpoint = '/permissions';

	constructor(token: string, orgId?: number) {
		super('/permissions', token, orgId);
	}
}
