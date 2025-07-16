import type { LayoutServerLoad } from './$types';
import { redirect } from '@sveltejs/kit';
import { toast } from 'svelte-sonner';

export const load: LayoutServerLoad = async ({ cookies, locals, url }) => {
	const { user } = locals;
	if (
		user.two_factor_enabled && 
		!user.two_factor_confirmed_at && 
		!url.pathname.startsWith('/settings/security')
	) {
		return redirect(302, '/settings/security#2fa');
	}
	return {
		...locals
	};
};
