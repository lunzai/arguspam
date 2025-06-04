import type { LoginRequest } from '$lib/types/auth.js';
import type { User } from '$lib/types/user.js';
import type { ApiResponse } from '$lib/types/response.js';
import { clientApi } from '$lib/api/client.js';

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

			// Clear any cached token in client API after successful login
			clientApi.clearAuthToken();

			return response;
		} catch (error) {
			throw error; // clientApi already handles error formatting
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
			throw error; // clientApi already handles error formatting
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
			
			// Clear the cached token in client API after logout
			clientApi.clearAuthToken();
		} catch (error) {
			// Clear token even if logout fails
			clientApi.clearAuthToken();
			throw error; // clientApi already handles error formatting
		}
	}
}

export const authService = new AuthService(); 