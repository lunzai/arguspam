import { BaseService } from './base.js';
import type { Request } from '$models/request';

export class RequestService extends BaseService<Request> {
	protected readonly endpoint = '/requests';

	constructor(token: string, orgId?: number) {
		super('/requests', token, orgId);
	}
}
