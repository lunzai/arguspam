import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { apiClient } from '$lib/server/api.js';
import { setAuthCookie } from '$lib/server/cookies.js';
import type { LoginResponse, ApiError } from '$lib/types/auth.js';

export const POST: RequestHandler = async ({ request, cookies }) => {
	try {
		const { email, password } = await request.json();

		if (!email || !password) {
			return json({ message: 'Email and password are required', errors: {} }, { status: 400 });
		}

		const response = (await apiClient.login(email, password)) as LoginResponse;

		// Set the token in an HttpOnly cookie
		setAuthCookie(cookies, response.data.token);

		// Return user data without the token (flatten the structure)
		return json({
			user: response.data.user
		});
	} catch (error) {
		console.error('Login error:', error);
		const apiError = error as ApiError;
		return json(
			{
				message: apiError.message || 'Login failed',
				errors: apiError.errors || {}
			},
			{ status: apiError.status || 500 }
		);
	}
};
