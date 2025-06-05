import type { LoginRequest } from '$types/auth.js';
import type { User } from '$models/user.js';
import type { ApiResponse } from '$types/api.js';
import { clientApi } from '$api/client.js';
import { orgStore } from '$stores/org.js';

export class AuthService {
	/**
	 * Login user via SvelteKit API route
	 */
	async login(credentials: LoginRequest): Promise<ApiResponse<{ user: User }>> {
		try {
			const response = await clientApi.internal().post<ApiResponse<{ user: User }>>(
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
	async me(): Promise<ApiResponse<User>> {
		try {
			const response = await clientApi.internal().get<ApiResponse<User>>(
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