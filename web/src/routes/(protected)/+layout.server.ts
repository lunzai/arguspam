import type { LayoutServerLoad } from './$types';
import { redirect } from '@sveltejs/kit';
import { authStore } from '$stores/auth.js';
import { orgStore } from '$stores/org';

export const load: LayoutServerLoad = async ({ cookies, locals, url }) => {
	const { user } = locals;
	const require2faSetup = user.two_factor_enabled && !user.two_factor_confirmed_at;
	const is2faRoute =
		url.pathname.startsWith('/settings/security') ||
		url.pathname.startsWith(`/users/${user.id}/security`);
	if (require2faSetup && !is2faRoute) {
		return redirect(302, '/settings/security#2fa');
	}
	return {
		...locals
	};
};
