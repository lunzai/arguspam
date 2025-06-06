import type { LoginRequest } from '$types/auth.js';
import type { User } from '$models/user.js';
import type { ApiResponse, Resource } from '$types/api.js';
import { clientApi } from '$api/client.js';
import { orgStore } from '$stores/org.js';
import type { Org } from '$models/org.js';

export interface LoginResponse {
	user: User;
	orgs: Org[];
	currentOrgId: number | null;
}

export class AuthService {
	/**
	 * Login user via SvelteKit API route
	 */
	async login(credentials: LoginRequest): Promise<ApiResponse<LoginResponse>> {
		try {
			const response = await clientApi.internal().post<ApiResponse<LoginResponse>>(
				'/api/auth/login', 
				credentials
			);
			clientApi.clearAuthToken();
			return response;
		} catch (error) {
			throw error;
		}
	}

	/**
	 * Get current user via SvelteKit API route
	 */
	async me(): Promise<Resource<User>> {
		try {
			const response = await clientApi.internal().get<Resource<User>>(
				'/api/auth/me'
			);
			return response;
		} catch (error) {
			throw error;
		}
	}

	/**
	 * Logout user via SvelteKit API route
	 */
	async logout(): Promise<void> {
		try {
			await clientApi.internal().post<void>(
				'/api/auth/logout',
				{}
			);
			clientApi.clearAuthToken();
			orgStore.reset();
		} catch (error) {
			clientApi.clearAuthToken();
			orgStore.reset();
			throw error;
		}
	}
}

export const authService = new AuthService(); 