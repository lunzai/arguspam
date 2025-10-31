import type { LayoutServerLoad } from './$types';
import { redirect } from '@sveltejs/kit';

export const load: LayoutServerLoad = async ({ cookies, locals, url }) => {
	const { me, currentOrgId } = locals;
	const require2faSetup = me.two_factor_enabled && !me.two_factor_confirmed_at;
	const is2faRoute =
		url.pathname.startsWith('/settings/security') ||
		url.pathname.startsWith(`/users/${me.id}/security`);
	if (require2faSetup && !is2faRoute) {
		return redirect(302, '/settings/security#2fa');
	}
	return {
		me,
		currentOrgId
	};
};
