import { redirect } from '@sveltejs/kit';
import type { LayoutServerLoad } from './$types';
import { getAuthToken } from '$lib/server/helpers/cookie.js';
import { authService } from '$lib/server/services/auth.js';
import type { User } from '$lib/shared/types/user.js';

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
		const user = await authService.me(token);

		return {
			user: user,
			url: url.pathname
		};
	} catch (error) {
		// Token is invalid or expired, clear cookie and redirect
		const { clearAuthCookie } = await import('$lib/server/helpers/cookie.js');
		clearAuthCookie(cookies);
		throw redirect(302, '/auth/login');
	}
}) satisfies LayoutServerLoad;
