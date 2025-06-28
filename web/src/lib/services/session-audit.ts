import { BaseService } from './base.js';
import type { SessionAudit } from '$models/session-audit';

export class SessionAuditService extends BaseService<SessionAudit> {
	protected readonly endpoint = '/session-audits';

	constructor(token: string, orgId?: number) {
		super('/session-audits', token, orgId);
	}
} 