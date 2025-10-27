import { BaseService } from './base.js';
import type { Session } from '$models/session';
import type {
	ApiSessionResource,
	SessionPermission,
	SessionPermissionResource
} from '$lib/resources/session.js';

export class SessionService extends BaseService<Session> {
	protected readonly endpoint = '/sessions';

	constructor(token: string, orgId?: number) {
		super('/sessions', token, orgId);
	}

	async permissions(id: number): Promise<SessionPermission> {
		try {
			const response = await this.api.get<SessionPermissionResource>(
				`${this.endpoint}/${id}/permissions`
			);
			return response.data;
		} catch (error) {
			return {
				canTerminate: false,
				canCancel: false,
				canStart: false,
				canEnd: false,
				canRetrieveSecret: false
			};
		}
	}

	async start(id: number): Promise<ApiSessionResource> {
		return await this.api.post(`${this.endpoint}/${id}/start`);
	}

	async end(id: number): Promise<ApiSessionResource> {
		return await this.api.put(`${this.endpoint}/${id}/end`);
	}

	async cancel(id: number): Promise<ApiSessionResource> {
		return await this.api.delete(`${this.endpoint}/${id}/cancel`);
	}

	async terminate(id: number): Promise<ApiSessionResource> {
		return await this.api.delete(`${this.endpoint}/${id}/terminate`);
	}

	async retrieveSecret(id: number): Promise<ApiSessionResource> {
		return await this.api.get(`${this.endpoint}/${id}/secret`);
	}
}
