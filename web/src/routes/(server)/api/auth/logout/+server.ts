import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { authService } from '$services/server/auth.js';
import { getAuthToken, clearAuthCookie } from '$server/helpers/cookie.js';
import { CURRENT_ORG_KEY } from '$env/static/private';
import type { ApiError } from '$types/error.js';

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

		// Clear the current org cookie
		cookies.delete(CURRENT_ORG_KEY, { path: '/' });

		return json({ message: 'Logged out successfully' });
	} catch (error) {
		const apiError = error as ApiError;

		// Clear cookie even if logout fails
		clearAuthCookie(cookies);

		// Clear the current org cookie
		cookies.delete(CURRENT_ORG_KEY, { path: '/' });

		return json(
			{ message: apiError.message || 'Logout failed' },
			{ status: apiError.status || 500 }
		);
	}
};
