import type { PageServerLoad } from './$types';
import { Rbac } from '$lib/rbac';
import { UserService } from '$lib/services/user';

export const load: PageServerLoad = async ({ locals, url }) => {
	new Rbac(locals.me).userViewAny();
    const page = url.searchParams.get('page') ? Number(url.searchParams.get('page')) : 1;
    const perPage = url.searchParams.get('per_page') ? Number(url.searchParams.get('per_page')) : 20;
    const sort = url.searchParams.get('sort') ? url.searchParams.get('sort')?.split(',') : [];
    const filter = url.searchParams.get('filter') ? JSON.parse(url.searchParams.get('filter') as string) : {};
    // const include = url.searchParams.get('include') ? url.searchParams.get('include')?.split(',') : [];
    const count = url.searchParams.get('count') ? url.searchParams.get('count')?.split(',') : [];
    console.log('ServerPageLoad', url.searchParams);
    const users = await new UserService(locals.authToken as string, locals.currentOrgId as number).findAll({
        page,
        perPage,
        sort,
        filter: {
            ...filter,
            status: 'active'
        },
        count,
        include: ['roles'],
    });
	return {
		title: 'Users',
        list: users.data,
        meta: users.meta,
	};
};
