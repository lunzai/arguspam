import type { PageServerLoad } from './$types';
import { Rbac } from '$lib/rbac';

export const load: PageServerLoad = async ({ locals }) => {
	new Rbac(locals.me).userViewAny();
	return {
		title: 'Users'
	};
};
