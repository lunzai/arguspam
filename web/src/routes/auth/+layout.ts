import { redirect } from '@sveltejs/kit';
import type { LayoutLoad } from './$types';

export const load = (async ({ url }) => {
    // TODO: Add proper auth check
    const isAuthenticated = false; // Replace with actual auth check

    // If user is authenticated and tries to access guest routes, redirect to dashboard
    if (isAuthenticated && url.pathname !== '/') {
        throw redirect(302, '/dashboard');
    }

    return {
        url: url.pathname
    };
}) satisfies LayoutLoad; 