import { redirect } from '@sveltejs/kit';
import type { LayoutServerLoad } from './$types';
import { getAuthToken } from '$lib/server/cookies.js';
import { apiClient } from '$lib/server/api.js';
import type { User } from '$lib/types/auth.js';

interface MeResponse {
	data: {
		user: User;
	};
}

export const load = (async ({ cookies, url }) => {
	const token = getAuthToken(cookies);

	if (!token) {
		throw redirect(302, '/auth/login');
	}

	try {
		// Verify token and get user data from Laravel API
		const response = (await apiClient.me(token)) as MeResponse;

		return {
			user: response.data.user,
			url: url.pathname
		};
	} catch (error) {
		// Token is invalid or expired, clear cookie and redirect
		const { clearAuthCookie } = await import('$lib/server/cookies.js');
		clearAuthCookie(cookies);
		throw redirect(302, '/auth/login');
	}
}) satisfies LayoutServerLoad;
