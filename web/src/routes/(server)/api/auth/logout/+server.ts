import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { authService } from '$lib/server/services/auth.js';
import { getAuthToken, clearAuthCookie } from '$lib/server/helpers/cookie.js';
import type { ApiError } from '$lib/shared/types/error.js';

export const POST: RequestHandler = async ({ cookies }) => {
	try {
		const token = getAuthToken(cookies);

		if (!token) {
			return json({ message: 'Not authenticated' }, { status: 401 });
		}

		// Call Laravel logout endpoint
		await authService.logout(token);

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
