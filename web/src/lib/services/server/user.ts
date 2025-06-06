import { serverApi } from '$api/server.js';
import type { ApiCollectionResponse } from '$types/api';
import type { Org } from '$models/org.js';
import { clientApi } from '$lib/api/client';

/**
 * User service class that handles user-related API calls
 */
export class UserService {
	private readonly endpoint = '/users';
	private readonly token: string;

	constructor(token: string) {
		this.token = token;
	}

	/**
	 * Get organizations for the authenticated user
	 */
	async getOrgs(): Promise<ApiCollectionResponse<Org>> {
		return serverApi.get<ApiCollectionResponse<Org>>(`${this.endpoint}/me/orgs`, this.token);
	}

	async checkOrgAccess(orgId: number): Promise<boolean> {
		try {
			await serverApi.get<boolean>(`${this.endpoint}/me/orgs/${orgId}`, this.token);
			return true;
		} catch (error) {
			return false;
		}
	}
}
