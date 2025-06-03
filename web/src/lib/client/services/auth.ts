import type { LoginRequest } from '$lib/shared/types/auth.js';
import type { User } from '$lib/shared/types/user.js';
import type { ApiError } from '$lib/shared/types/error.js';

export class AuthService {
	/**
	 * Login user via SvelteKit API route
	 */
	async login(credentials: LoginRequest): Promise<{ user: User }> {
		const response = await fetch('/api/auth/login', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(credentials)
		});

		const data = await response.json();

		if (!response.ok) {
			throw {
				message: data.message,
				status: response.status,
				errors: data.errors
			} as ApiError;
		}

		return data;
	}

	/**
	 * Get current user via SvelteKit API route
	 */
	async me(): Promise<{ user: User }> {
		const response = await fetch('/api/auth/me');
		const data = await response.json();

		if (!response.ok) {
			throw {
				message: data.message,
				status: response.status
			} as ApiError;
		}

		return data;
	}

	/**
	 * Logout user via SvelteKit API route
	 */
	async logout(): Promise<void> {
		const response = await fetch('/api/auth/logout', {
			method: 'POST'
		});

		if (!response.ok) {
			const data = await response.json();
			throw {
				message: data.message,
				status: response.status
			} as ApiError;
		}
	}
}

export const authService = new AuthService();
