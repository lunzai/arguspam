import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { authService } from '$services/server/auth.js';
import { getAuthToken } from '$server/helpers/cookie.js';
import type { ApiError } from '$types/error.js';

export const GET: RequestHandler = async ({ cookies }) => {
	try {
		const token = getAuthToken(cookies);
		if (!token) {
			return json({ message: 'Not authenticated' }, { status: 401 });
		}
		const user = await authService.me(token);
		return json({
			data: user
		});
	} catch (error) {
		const apiError = error as ApiError;
		return json(
			{ message: apiError.message || 'Failed to get user' },
			{ status: apiError.status || 500 }
		);
	}
};
