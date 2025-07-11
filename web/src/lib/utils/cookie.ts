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

// Base cookie options used by both setCookie and clearCookie
const baseCookieOptions = {
	path: '/',
	httpOnly: true,
	secure: true,
	sameSite: COOKIE_SAME_SITE as 'strict' | 'lax' | 'none'
};

export function setCookie(
	cookies: Cookies,
	key: string,
	value: string,
	options: Partial<CookieOptions> = {}
) {
	const defaultOptions = {
		...baseCookieOptions,
		maxAge: parseInt(COOKIE_EXPIRY)
	};

	cookies.set(key, value, { ...defaultOptions, ...options });
}

export function getCookie(cookies: Cookies, key: string): string | undefined {
	return cookies.get(key);
}

export function clearCookie(cookies: Cookies, key: string, options: Partial<CookieOptions> = {}) {
	cookies.delete(key, { ...baseCookieOptions, ...options });
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
export function setCurrentOrgId(cookies: Cookies, orgId: string | number) {
	setCookie(cookies, COOKIE_CURRENT_ORG_KEY, orgId.toString());
}

export function getCurrentOrgId(cookies: Cookies): number | null {
	const orgId = getCookie(cookies, COOKIE_CURRENT_ORG_KEY);
	return orgId ? parseInt(orgId) : null;
}

export function clearCurrentOrgId(cookies: Cookies) {
	clearCookie(cookies, COOKIE_CURRENT_ORG_KEY);
}
