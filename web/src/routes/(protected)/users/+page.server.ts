import type { PageServerLoad } from './$types';
import { createListLoad } from '$components/datatable';
import type { Me } from '$lib/models/user';
import { Rbac } from '$lib/rbac';
import { UserService } from '$lib/services/user';

export const load: PageServerLoad = createListLoad({
	serviceFactory: (token, orgId) => new UserService(token, orgId),
	rbacCheck: (me: Me) => new Rbac(me).userViewAny(),
	defaultParams: { perPage: 20, include: ['roles'], filter: { status: 'active' } },
	depends: 'users:list',
	title: 'Users'
});