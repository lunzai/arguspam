import { serverApi } from '$api/server.js';
import type { ApiCollectionResponse } from '$types/api';
import type { Org } from '$models/org.js';

/**
 * User service class that handles user-related API calls
 */
export class UserService {
	/**
	 * Get organizations for the authenticated user
	 */
	async getOrgs(token: string): Promise<ApiCollectionResponse<Org>> {
		return serverApi.get<ApiCollectionResponse<Org>>('/users/me/orgs', token);
	}
}

// Default user service instance
export const userService = new UserService(); 