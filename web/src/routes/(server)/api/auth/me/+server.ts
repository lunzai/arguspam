import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { authService } from '$lib/server/services/auth.js';
import { getAuthToken } from '$lib/server/helpers/cookie.js';
import type { ApiError } from '$lib/shared/types/error.js';

export const GET: RequestHandler = async ({ cookies }) => {
	try {
		const token = getAuthToken(cookies);

		if (!token) {
			return json({ message: 'Not authenticated' }, { status: 401 });
		}

		const user = await authService.me(token);

		return json({ user });
	} catch (error) {
		const apiError = error as ApiError;
		return json(
			{ message: apiError.message || 'Failed to get user' },
			{ status: apiError.status || 500 }
		);
	}
};
