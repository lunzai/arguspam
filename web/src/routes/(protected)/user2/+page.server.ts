import type { PageServerLoad } from './$types';
import { Rbac } from '$lib/rbac';
import { UserService } from '$lib/services/user';

export const load: PageServerLoad = async ({ locals }) => {
	new Rbac(locals.me).userViewAny();
    const users = await new UserService(locals.authToken as string, locals.currentOrgId as number).findAll({
        perPage: 20,
        filter: {
            status: 'active'
        },
        include: ['roles'],
    });
	return {
		title: 'Users',
        list: users.data,
        meta: users.meta,
	};
};
