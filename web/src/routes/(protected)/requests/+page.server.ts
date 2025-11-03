import { Rbac } from '$lib/rbac';
import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals, depends }) => {
	depends('requests:list');
	new Rbac(locals.me).requestView();
	return {
		title: 'Requests'
	};
};
