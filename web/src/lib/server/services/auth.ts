import { apiClient } from '../api.js';
import type { LoginResponse as TypedLoginResponse } from '$lib/shared/types/auth.js';
import type { User } from '$lib/shared/types/user.js';

// Use the existing LoginResponse type from auth types
export type LoginResponse = TypedLoginResponse;

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

	// /**
	//  * Register a new user
	//  */
	// async register(userData: {
	// 	email: string;
	// 	password: string;
	// 	name: string;
	// 	password_confirmation: string;
	// }): Promise<LoginResponse> {
	// 	return apiClient.post<LoginResponse>('/auth/register', userData);
	// }

	// /**
	//  * Request password reset
	//  */
	// async forgotPassword(email: string): Promise<{ message: string }> {
	// 	return apiClient.post<{ message: string }>('/auth/forgot-password', { email });
	// }

	// /**
	//  * Reset password
	//  */
	// async resetPassword(data: {
	// 	token: string;
	// 	email: string;
	// 	password: string;
	// 	password_confirmation: string;
	// }): Promise<{ message: string }> {
	// 	return apiClient.post<{ message: string }>('/auth/reset-password', data);
	// }

	// /**
	//  * Refresh authentication token
	//  */
	// async refreshToken(token: string): Promise<{ token: string }> {
	// 	return apiClient.post<{ token: string }>('/auth/refresh', {}, token);
	// }

	// /**
	//  * Verify email address
	//  */
	// async verifyEmail(token: string, id: string, hash: string): Promise<{ message: string }> {
	// 	return apiClient.post<{ message: string }>(`/auth/email/verify/${id}/${hash}`, {}, token);
	// }

	// /**
	//  * Resend email verification
	//  */
	// async resendVerification(token: string): Promise<{ message: string }> {
	// 	return apiClient.post<{ message: string }>('/auth/email/verification-notification', {}, token);
	// }
}

// Default auth service instance
export const authService = new AuthService(); 