import type { Cookies } from '@sveltejs/kit';
import {
	AUTH_TOKEN_KEY,
	AUTH_TOKEN_EXPIRY,
	AUTH_COOKIE_SAME_SITE
} from '$env/static/private';

export function setAuthCookie(cookies: Cookies, token: string) {
	cookies.set(AUTH_TOKEN_KEY, token, {
		path: '/',
		httpOnly: true,
		secure: true,
		sameSite: AUTH_COOKIE_SAME_SITE as 'strict' | 'lax' | 'none',
		maxAge: parseInt(AUTH_TOKEN_EXPIRY)
	});
}

export function getAuthToken(cookies: Cookies): string | undefined {
	return cookies.get(AUTH_TOKEN_KEY);
}

export function clearAuthCookie(cookies: Cookies) {
	cookies.delete(AUTH_TOKEN_KEY, {
		path: '/',
		httpOnly: true,
		secure: true,
		sameSite: AUTH_COOKIE_SAME_SITE as 'strict' | 'lax' | 'none'
	});
}
