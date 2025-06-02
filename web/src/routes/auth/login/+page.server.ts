import { fail, redirect } from '@sveltejs/kit';
import type { Actions, PageServerLoad } from './$types';
import axios from 'axios';
import { AUTH_TOKEN_EXPIRY, ALLOWED_HOSTS, AUTH_COOKIE_SAME_SITE } from '$env/static/private';

// Environment configuration - should match server API
const API_URL = 'https://api.argus.pam'; // TODO: Add API_URL to private env
const AUTH_TOKEN_KEY = 'auth_token'; // TODO: Add AUTH_TOKEN_KEY to private env

export const load: PageServerLoad = async ({ cookies }) => {
	// If user is already logged in, redirect to protected area
	const token = cookies.get(AUTH_TOKEN_KEY);
	if (token) {
		// Optionally verify token is still valid here
		redirect(302, '/dashboard');
	}
	
	return {};
};

export const actions: Actions = {
	login: async ({ request, cookies }) => {
		const data = await request.formData();
		const email = data.get('email')?.toString();
		const password = data.get('password')?.toString();

		try {
			const response = await axios.post(`${API_URL}/auth/login`, {
				email,
				password
			});

			const { token, user } = response.data.data;

			// Set the token in an HTTP-only cookie for security
			cookies.set(AUTH_TOKEN_KEY, token, {
				path: '/',
				httpOnly: true,
				secure: !ALLOWED_HOSTS.includes('127.0.0.1'),
				sameSite: AUTH_COOKIE_SAME_SITE as 'strict' | 'lax' | 'none',
				maxAge: parseInt(AUTH_TOKEN_EXPIRY)
			});

			// Set user data in a readable cookie (include all user fields)
			cookies.set('user_data', JSON.stringify({
				id: user.id,
				name: user.name,
				email: user.email,
				email_verified_at: user.email_verified_at,
				two_factor_enabled: user.two_factor_enabled,
				two_factor_confirmed_at: user.two_factor_confirmed_at,
				status: user.status
			}), {
				path: '/',
				httpOnly: false, // Allow client-side access
				secure: !ALLOWED_HOSTS.includes('127.0.0.1'),
				sameSite: AUTH_COOKIE_SAME_SITE as 'strict' | 'lax' | 'none',
				maxAge: parseInt(AUTH_TOKEN_EXPIRY)
			});

			redirect(302, '/dashboard');
		} catch (error: any) {
			if (error.response?.status === 422) {
				// Validation errors
				return fail(422, {
					email,
					errors: error.response.data.errors
				});
			} else if (error.response?.status === 401) {
				// Invalid credentials
				return fail(401, {
					email,
					error: 'Invalid email or password'
				});
			} else {
				// Network or other errors
				return fail(500, {
					email,
					error: 'An error occurred. Please try again.'
				});
			}
		}
	}
}; 