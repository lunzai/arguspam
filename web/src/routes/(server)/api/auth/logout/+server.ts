import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { apiClient } from '$lib/server/api.js';
import { getAuthToken, clearAuthCookie } from '$lib/server/cookies.js';
import type { ApiError } from '$lib/types/auth.js';

export const POST: RequestHandler = async ({ cookies }) => {
	try {
		const token = getAuthToken(cookies);

		if (!token) {
			return json({ message: 'Not authenticated' }, { status: 401 });
		}

		// Call Laravel logout endpoint
		await apiClient.logout(token);

		// Clear the auth cookie
		clearAuthCookie(cookies);

		return json({ message: 'Logged out successfully' });
	} catch (error) {
		const apiError = error as ApiError;

		// Clear cookie even if logout fails
		clearAuthCookie(cookies);

		return json(
			{ message: apiError.message || 'Logout failed' },
			{ status: apiError.status || 500 }
		);
	}
};
