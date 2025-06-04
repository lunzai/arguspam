import type { LayoutServerLoad } from './$types';
import { redirect } from '@sveltejs/kit';
import { getAuthToken } from '$server/helpers/cookie.js';
import { authService } from '$services/server/auth.js';
import type { User } from '$models/user.js';
import { PUBLIC_AUTH_LOGIN_PATH } from '$env/static/public';

export const load: LayoutServerLoad = async ({ cookies }) => {
	const token = getAuthToken(cookies);
	if (!token) {
		throw redirect(302, PUBLIC_AUTH_LOGIN_PATH);
	}
	try {
		const user: User = await authService.me(token);
		return {
			user
		};
	} catch (error) {
		// If token is invalid, redirect to login
		throw redirect(302, PUBLIC_AUTH_LOGIN_PATH);
	}
};
