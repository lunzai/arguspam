import { serverApi } from '$api/server.js';
import type { ApiResponse } from '$types/api';
import type { LoginResponse } from '$types/auth.js';
import type { User } from '$models/user.js';
import { PUBLIC_AUTH_LOGOUT_PATH, PUBLIC_AUTH_LOGIN_PATH } from '$env/static/public';

/**
 * Auth service class that handles authentication-related API calls
 */
export class AuthService {
	/**
	 * Login to the API
	 */
	async login(email: string, password: string): Promise<LoginResponse> {
		return serverApi.post<LoginResponse>(PUBLIC_AUTH_LOGIN_PATH, { email, password });
	}

	/**
	 * Get current authenticated user
	 */
	async me(token: string): Promise<User> {
		const response = await serverApi.get<ApiResponse<{ user: User }>>('/auth/me', token);
		return response.data.user;
	}

	/**
	 * Logout from the API
	 */
	async logout(token: string): Promise<void> {
		return serverApi.post<void>(PUBLIC_AUTH_LOGOUT_PATH, {}, token);
	}
}

// Default auth service instance
export const authService = new AuthService();
