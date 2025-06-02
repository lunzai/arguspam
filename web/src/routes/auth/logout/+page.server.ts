import { redirect } from '@sveltejs/kit';
import type { Actions, PageServerLoad } from './$types';
import axios from 'axios';

// Environment configuration - should match server API
const API_URL = 'https://api.argus.pam'; // TODO: Add API_URL to private env
const AUTH_TOKEN_KEY = 'auth_token'; // TODO: Add AUTH_TOKEN_KEY to private env

export const load: PageServerLoad = async () => {
	// Redirect immediately since this should be an action, not a page
	redirect(302, '/');
};

export const actions: Actions = {
	default: async ({ cookies }) => {
		const token = cookies.get(AUTH_TOKEN_KEY);
		
		if (token) {
			try {
				// Call logout API
				await axios.post(`${API_URL}/auth/logout`, {}, {
					headers: {
						'Authorization': `Bearer ${token}`
					}
				});
			} catch (error) {
				// Even if API call fails, we still want to clear local cookies
				console.error('Logout API error:', error);
			}
		}

		// Clear cookies
		cookies.delete(AUTH_TOKEN_KEY, { path: '/' });
		cookies.delete('user_data', { path: '/' });

		redirect(302, '/auth/login');
	}
}; 