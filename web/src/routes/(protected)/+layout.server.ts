import type { LayoutServerLoad } from './$types';
import { redirect } from '@sveltejs/kit';
import { getAuthToken } from '$lib/server/helpers/cookie.js';
import { authService } from '$lib/services/server/auth.js';
import type { User } from '$lib/types/user.js';

export const load: LayoutServerLoad = async ({ cookies }) => {
	const token = getAuthToken(cookies);
	
	if (!token) {
		throw redirect(302, '/login');
	}

	try {
		const user: User = await authService.me(token);
		return {
			user
		};
	} catch (error) {
		// If token is invalid, redirect to login
		throw redirect(302, '/login');
	}
};
