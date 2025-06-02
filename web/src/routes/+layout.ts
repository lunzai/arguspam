import { redirect } from '@sveltejs/kit';
import type { LayoutLoad } from './$types';
import { auth } from '$lib/stores/auth';

export const load: LayoutLoad = async ({ data, url }) => {
    const { isAuthenticated, user, isPublicPath } = data;

    // Initialize auth store on client side
    if (typeof window !== 'undefined') {
        auth.init();
        if (user) {
            auth.setUser(user);
        }
    }

    // If not authenticated and trying to access protected route
    if (!isAuthenticated && !isPublicPath) {
        redirect(302, '/auth/login');
    }

    // If authenticated and trying to access auth pages, redirect to home
    if (isAuthenticated && url.pathname.startsWith('/auth/login')) {
        redirect(302, '/');
    }

    return {
        isAuthenticated,
        user,
        url: url.pathname
    };
}; 