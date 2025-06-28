import { BaseService } from './base.js';
import type { Session } from '$models/session';

export class SessionService extends BaseService<Session> {
	protected readonly endpoint = '/sessions';

	constructor(token: string, orgId?: number) {
		super('/sessions', token, orgId);
	}
} 