import { fail, redirect } from '@sveltejs/kit';
import type { Actions, PageServerLoad } from './$types';
import config from '$lib/config';
import { AuthRepository } from '$lib/api/repositories/auth.repository';

export const load: PageServerLoad = async ({ cookies, url }) => {
    // If already authenticated, redirect to dashboard
    const authToken = cookies.get(config.auth.tokenKey);
    if (authToken) {
        // Validate token with backend
        try {
            const response = await fetch(`${process.env.VITE_API_URL}/users/me`, {
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            if (response.ok) {
                // User is authenticated, redirect to dashboard or return URL
                const redirectTo = url.searchParams.get('redirectTo') || '/dashboard';
                throw redirect(302, redirectTo);
            }
        } catch (error) {
            // Token is invalid, clear it
            cookies.delete(config.auth.tokenKey, { path: '/' });
        }
    }
    
    return {
        redirectTo: url.searchParams.get('redirectTo')
    };
};

export const actions: Actions = {
    login: async ({ request, cookies, url }) => {
        const formData = await request.formData();
        const email = formData.get('email')?.toString();
        const password = formData.get('password')?.toString();
        
        if (!email || !password) {
            return fail(400, {
                error: 'Email and password are required',
                email
            });
        }
        
        try {
            // Use AuthRepository for login
            const response = await AuthRepository.serverLogin({ email, password });
            
            // Set HTTP-only cookie with the token
            cookies.set(config.auth.tokenKey, response.data.token, {
                path: config.auth.cookieOptions.path,
                maxAge: config.auth.tokenExpiry,
                httpOnly: true,
                secure: config.auth.cookieOptions.secure,
                sameSite: config.auth.cookieOptions.sameSite
            });
            
            // Redirect to dashboard or return URL
            const redirectTo = url.searchParams.get('redirectTo') || '/dashboard';
            throw redirect(302, redirectTo);
            
        } catch (error: any) {
            // If it's a redirect, let it through
            if (error.status === 302) {
                throw error;
            }
            
            console.error('Login error:', error);
            
            // Handle different types of errors
            if (error.response) {
                if (error.response.status === 422) {
                    // Validation errors
                    return fail(422, {
                        errors: error.response.data.errors,
                        email
                    });
                } else if (error.response.status === 401) {
                    // Authentication error
                    return fail(401, {
                        error: 'Invalid email or password',
                        email
                    });
                } else {
                    // Other API errors
                    return fail(error.response.status, {
                        error: 'An error occurred. Please try again.',
                        email
                    });
                }
            }
            
            // Network or other errors
            return fail(500, {
                error: 'An error occurred. Please try again.',
                email
            });
        }
    }
}; 