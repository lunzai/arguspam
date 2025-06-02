import type { LayoutServerLoad } from './$types';

// Environment configuration - should match server API
const AUTH_TOKEN_KEY = 'auth_token'; // TODO: Add AUTH_TOKEN_KEY to private env

export const load: LayoutServerLoad = async ({ cookies, url }) => {
	const token = cookies.get(AUTH_TOKEN_KEY);
	const userDataCookie = cookies.get('user_data');
	
	let user = null;
	if (userDataCookie) {
		try {
			user = JSON.parse(userDataCookie);
		} catch (error) {
			console.error('Error parsing user data cookie:', error);
		}
	}
	
	const isAuthenticated = !!(token && user);
	
	// Define public paths that don't require authentication
	const publicPaths = ['/auth/login', '/auth/signup', '/auth/forgot-password', '/auth/forget-password'];
	const isPublicPath = publicPaths.some(path => url.pathname.startsWith(path));
	
	return {
		isAuthenticated,
		user,
		url: url.pathname,
		isPublicPath
	};
}; 