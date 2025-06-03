import { apiClient } from '$lib/api/server.js';
import type { LoginResponse } from '$lib/types/auth.js';
import type { User } from '$lib/types/user.js';

/**
 * Auth service class that handles authentication-related API calls
 */
export class AuthService {
	/**
	 * Login to the API
	 */
	async login(email: string, password: string): Promise<LoginResponse> {
		return apiClient.post<LoginResponse>('/auth/login', { email, password });
	}

	/**
	 * Get current authenticated user
	 */
	async me(token: string): Promise<User> {
		return apiClient.get<User>('/auth/me', token);
	}

	/**
	 * Logout from the API
	 */
	async logout(token: string): Promise<void> {
		return apiClient.post<void>('/auth/logout', {}, token);
	}
}

// Default auth service instance
export const authService = new AuthService(); 