import { redirect } from '@sveltejs/kit';
import type { LayoutLoad } from './$types';

export const load = (async ({ url }) => {
    // TODO: Add proper auth check
    // const isAuthenticated = true; // Replace with actual auth check

    // if (!isAuthenticated) {
    //     throw redirect(302, '/auth/login');
    // }

    // return {
    //     url: url.pathname
    // };
}) satisfies LayoutLoad; 