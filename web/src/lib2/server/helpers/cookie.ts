import type { Cookies } from '@sveltejs/kit';
import {
	COOKIE_EXPIRY,
	COOKIE_SAME_SITE,
	COOKIE_TOKEN_KEY,
	COOKIE_CURRENT_ORG_KEY
} from '$env/static/private';

interface CookieOptions {
	path?: string;
	httpOnly?: boolean;
	secure?: boolean;
	sameSite?: 'strict' | 'lax' | 'none';
	maxAge?: number;
}

/**
 * Generic cookie setter
 */
export function setCookie(
	cookies: Cookies,
	key: string,
	value: string,
	options: Partial<CookieOptions> = {}
) {
	const defaultOptions = {
		path: '/',
		httpOnly: true,
		secure: true,
		sameSite: COOKIE_SAME_SITE as 'strict' | 'lax' | 'none',
		maxAge: parseInt(COOKIE_EXPIRY)
	};

	cookies.set(key, value, { ...defaultOptions, ...options });
}

/**
 * Generic cookie getter
 */
export function getCookie(cookies: Cookies, key: string): string | undefined {
	return cookies.get(key);
}

/**
 * Generic cookie clearer
 */
export function clearCookie(cookies: Cookies, key: string, options: Partial<CookieOptions> = {}) {
	const defaultOptions = {
		path: '/',
		httpOnly: true,
		secure: true,
		sameSite: COOKIE_SAME_SITE as 'strict' | 'lax' | 'none'
	};

	cookies.delete(key, { ...defaultOptions, ...options });
}

// Auth-specific methods
export function setAuthToken(cookies: Cookies, token: string) {
	setCookie(cookies, COOKIE_TOKEN_KEY, token);
}

export function getAuthToken(cookies: Cookies): string | undefined {
	return getCookie(cookies, COOKIE_TOKEN_KEY);
}

export function clearAuthCookie(cookies: Cookies) {
	clearCookie(cookies, COOKIE_TOKEN_KEY);
}

// Org-specific methods
export function setCurrentOrgCookie(cookies: Cookies, orgId: string | number) {
	setCookie(cookies, COOKIE_CURRENT_ORG_KEY, orgId.toString());
}

export function getCurrentOrg(cookies: Cookies): number | null {
	const orgId = getCookie(cookies, COOKIE_CURRENT_ORG_KEY);
	return orgId ? parseInt(orgId) : null;
}

export function clearCurrentOrg(cookies: Cookies) {
	clearCookie(cookies, COOKIE_CURRENT_ORG_KEY);
}
