import { redirect } from '@sveltejs/kit';
import type { Actions, PageServerLoad } from './$types';
import config from '$lib/config';

export const load: PageServerLoad = async ({ cookies }) => {
    // Clear the auth cookie
    cookies.delete(config.auth.tokenKey, { path: '/' });
    
    // Redirect to login
    throw redirect(302, '/auth/login');
};

export const actions: Actions = {
    default: async ({ cookies }) => {
        // Clear the auth cookie
        cookies.delete(config.auth.tokenKey, { path: '/' });
        
        // Redirect to login
        throw redirect(302, '/auth/login');
    }
}; 