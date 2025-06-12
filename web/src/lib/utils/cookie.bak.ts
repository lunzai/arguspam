import type { Cookies } from '@sveltejs/kit';
import {
	COOKIE_EXPIRY,
	COOKIE_SAME_SITE,
	COOKIE_TOKEN_KEY,
	COOKIE_CURRENT_ORG_KEY
} from '$env/static/private';

class CookieManager {
	private cookies: Cookies;
	
	// Fixed options for security and simplicity
	private readonly options = {
		path: '/',
		httpOnly: true,
		secure: true,
		sameSite: COOKIE_SAME_SITE as 'strict' | 'lax' | 'none',
		maxAge: parseInt(COOKIE_EXPIRY)
	};

	constructor(cookies: Cookies) {
		this.cookies = cookies;
	}

	/**
	 * Set a cookie
	 */
	set(key: string, value: string) {
		this.cookies.set(key, value, this.options);
	}

	/**
	 * Get a cookie
	 */
	get(key: string): string | undefined {
		return this.cookies.get(key);
	}

	/**
	 * Clear/delete a cookie
	 */
	clear(key: string) {
		const { maxAge, ...deleteOptions } = this.options;
		this.cookies.delete(key, deleteOptions);
	}

	// Auth-specific methods
	setAuthToken(token: string) {
		this.set(COOKIE_TOKEN_KEY, token);
	}

	getAuthToken(): string | undefined {
		return this.get(COOKIE_TOKEN_KEY);
	}

	clearAuthToken() {
		this.clear(COOKIE_TOKEN_KEY);
	}

	// Org-specific methods
	setCurrentOrgId(orgId: string | number) {
		this.set(COOKIE_CURRENT_ORG_KEY, orgId.toString());
	}

	getCurrentOrgId(): number | null {
		const orgId = this.get(COOKIE_CURRENT_ORG_KEY);
		return orgId ? parseInt(orgId) : null;
	}

	clearCurrentOrgId() {
		this.clear(COOKIE_CURRENT_ORG_KEY);
	}

	/**
	 * Clear all auth-related cookies (useful for logout)
	 */
	clearAuth() {
		this.clearAuthToken();
		this.clearCurrentOrgId();
	}
}

/**
 * Factory function to create a cookie manager instance
 */
export function createCookieManager(cookies: Cookies): CookieManager {
	return new CookieManager(cookies);
}

// Export the class for direct instantiation if needed
export { CookieManager };