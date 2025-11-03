import type { PageServerLoad } from './$types';
import { Rbac } from '$lib/rbac';

export const load: PageServerLoad = async ({ locals, depends }) => {
	depends('sessions:list');
	new Rbac(locals.me).sessionView();
	return {
		title: 'Sessions'
	};
};
