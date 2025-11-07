import { redirect, type Handle, type HandleServerError } from '@sveltejs/kit';
import { sequence } from '@sveltejs/kit/hooks';
import {
	clearAuthCookie,
	clearCurrentOrgId,
	getAuthToken,
	getCurrentOrgId,
	setAuthToken,
	setCurrentOrgId
} from '$utils/cookie';
import { PUBLIC_AUTH_LOGIN_PATH } from '$env/static/public';
import { AuthService } from '$lib/services/auth';
import type { ApiMeResource } from '$resources/user';
import type { Me } from '$models/user';
import { dev } from '$app/environment';

// Security headers handler
const securityHeaders: Handle = async ({ event, resolve }) => {
	const response = await resolve(event, {
		filterSerializedResponseHeaders: (name) => {
			// Allow all headers through
			return true;
		}
	});

	if (!dev) {
		// HSTS - Force HTTPS (fixes: Strict-Transport-Security Header Not Set)
		response.headers.set(
			'Strict-Transport-Security',
			'max-age=31536000; includeSubDomains; preload'
		);

		// Prevent MIME type sniffing (fixes: X-Content-Type-Options Header Missing)
		response.headers.set('X-Content-Type-Options', 'nosniff');

		// Clickjacking protection (fixes: Missing Anti-clickjacking Header)
		response.headers.set('X-Frame-Options', 'DENY');

		// Referrer policy
		response.headers.set('Referrer-Policy', 'strict-origin-when-cross-origin');

		// Permissions policy
		response.headers.set(
			'Permissions-Policy',
			'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()'
		);

		// Legacy XSS protection
		response.headers.set('X-XSS-Protection', '1; mode=block');

		// Cache control for security (fixes: Re-examine Cache-control Directives)
		const url = new URL(event.request.url);
		if (url.pathname.startsWith('/api') || url.pathname.includes('login')) {
			// No caching for API and sensitive pages
			response.headers.set(
				'Cache-Control',
				'no-store, no-cache, must-revalidate, proxy-revalidate'
			);
			response.headers.set('Pragma', 'no-cache');
			response.headers.set('Expires', '0');
		} else if (url.pathname.startsWith('/_app/') || url.pathname.match(/\.(js|css|woff2?)$/)) {
			// Long cache for immutable assets
			response.headers.set('Cache-Control', 'public, max-age=31536000, immutable');
		}

		// Ensure Content-Type is set (fixes: Content-Type Header Missing)
		if (!response.headers.has('Content-Type')) {
			if (url.pathname.endsWith('.js')) {
				response.headers.set('Content-Type', 'application/javascript; charset=utf-8');
			} else if (url.pathname.endsWith('.css')) {
				response.headers.set('Content-Type', 'text/css; charset=utf-8');
			} else if (url.pathname.endsWith('.json')) {
				response.headers.set('Content-Type', 'application/json; charset=utf-8');
			} else if (!url.pathname.includes('.')) {
				response.headers.set('Content-Type', 'text/html; charset=utf-8');
			}
		}
	}

	return response;
};

// Authentication handler
const authentication: Handle = async ({ event, resolve }) => {
	if (event.route.id?.startsWith('/(protected)') || event.route.id?.startsWith('/(server)/api')) {
		const authToken = getAuthToken(event.cookies);
		let currentOrgId = getCurrentOrgId(event.cookies);

		if (!authToken) {
			return redirect(302, PUBLIC_AUTH_LOGIN_PATH);
		}
		try {
			const authService = new AuthService(authToken);
			const meResource: ApiMeResource = await authService.me();
			const me = meResource.data.attributes as Me;
			if (!currentOrgId) {
				currentOrgId = me.orgs[0]?.id || null;
				if (currentOrgId) {
					setCurrentOrgId(event.cookies, currentOrgId);
				}
			}
			event.locals.me = me;
			event.locals.currentOrgId = currentOrgId;
			event.locals.authToken = authToken;
			setAuthToken(event.cookies, authToken);
		} catch (error) {
			clearAuthCookie(event.cookies);
			clearCurrentOrgId(event.cookies);
			return redirect(302, PUBLIC_AUTH_LOGIN_PATH);
		}
	}
	return await resolve(event);
};

// Combine handlers
export const handle = sequence(securityHeaders, authentication);

// Error handler (fixes: Information Disclosure)
export const handleError: HandleServerError = async ({ error, event, status, message }) => {
	// Log errors securely without exposing details
	console.error('Server error:', {
		status,
		path: event.url.pathname,
		// Don't log full error in production
		...(dev ? { error } : {})
	});

	// Don't expose internal error details in production
	if (!dev) {
		return {
			message: 'An unexpected error occurred'
		};
	}

	return { message };
};
