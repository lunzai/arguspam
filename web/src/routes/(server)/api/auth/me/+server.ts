import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { apiClient } from '$lib/server/api.js';
import { getAuthToken } from '$lib/server/cookies.js';
import type { ApiError } from '$lib/types/auth.js';

export const GET: RequestHandler = async ({ cookies }) => {
	try {
		const token = getAuthToken(cookies);

		if (!token) {
			return json({ message: 'Not authenticated' }, { status: 401 });
		}

		const user = await apiClient.me(token);

		return json({ user });
	} catch (error) {
		const apiError = error as ApiError;
		return json(
			{ message: apiError.message || 'Failed to get user' },
			{ status: apiError.status || 500 }
		);
	}
};
