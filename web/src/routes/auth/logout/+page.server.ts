import { fail, type Actions } from '@sveltejs/kit';
import { AuthService } from '$services/auth';
import type { PageServerLoad } from './$types';
import { clearAuthCookie, clearCurrentOrgId, getAuthToken } from '$utils/cookie';
import { redirect } from '@sveltejs/kit';

export const load: PageServerLoad = async ({ cookies }) => {
	const token = getAuthToken(cookies);
	if (!token) {
		return redirect(302, '/auth/login');
	}
};

export const actions: Actions = {
	default: async ({ cookies }) => {
        try {
            const token = getAuthToken(cookies) as string;
            const authService = new AuthService(token);
            await authService.logout();
            clearAuthCookie(cookies);
            clearCurrentOrgId(cookies);
            return redirect(302, '/auth/login');
        } catch (error) {
            return fail(401, { error: 'Logout failed' });
        }
	}
};