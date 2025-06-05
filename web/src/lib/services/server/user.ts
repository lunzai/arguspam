import { serverApi } from '$api/server.js';
import type { ApiResponse } from '$types/api';
import type { Org } from '$models/org.js';

/**
 * User service class that handles user-related API calls
 */
export class UserService {
	/**
	 * Get organizations for the authenticated user
	 */
	async getOrgs(token: string): Promise<ApiResponse<Org[]>> {
		return serverApi.get<ApiResponse<Org[]>>('/users/me/orgs', token);
	}
}

// Default user service instance
export const userService = new UserService(); 