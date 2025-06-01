import { redirect } from '@sveltejs/kit';
import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ cookies, url }) => {
    // Get the auth token from cookies
    const authToken = cookies.get('auth_token');
    
    // If no token, redirect to login
    if (!authToken) {
        throw redirect(302, `/auth/login?redirectTo=${encodeURIComponent(url.pathname)}`);
    }

    // Validate token with backend by fetching user data
    try {
        const response = await fetch(`${process.env.VITE_API_URL}/users/me`, {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            // Token is invalid, clear it and redirect
            cookies.delete('auth_token', { path: '/' });
            throw redirect(302, `/auth/login?redirectTo=${encodeURIComponent(url.pathname)}`);
        }

        const userData = await response.json();
        
        // Return user data to be available in all protected routes
        return {
            user: userData.data.attributes,
            isAuthenticated: true
        };
    } catch (error) {
        // Network error or invalid response, clear token and redirect
        cookies.delete('auth_token', { path: '/' });
        throw redirect(302, `/auth/login?redirectTo=${encodeURIComponent(url.pathname)}`);
    }
}; 