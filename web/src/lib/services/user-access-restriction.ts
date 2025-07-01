import { BaseService } from './base.js';
import type { UserAccessRestriction } from '$models/user-access-restriction';

export class UserAccessRestrictionService extends BaseService<UserAccessRestriction> {
	protected readonly endpoint = '/user-access-restrictions';

	constructor(token: string, orgId?: number) {
		super('/user-access-restrictions', token, orgId);
	}
}
