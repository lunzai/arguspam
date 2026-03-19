import type { PageServerLoad } from './$types';
import { createListLoad } from '$components/datatable';
import type { Me } from '$lib/models/user';
import { Rbac } from '$lib/rbac';
import { PermissionService } from '$lib/services/permission';

export const load: PageServerLoad = createListLoad({
	serviceFactory: (token, orgId) => new PermissionService(token, orgId),
	rbacCheck: (me: Me) => new Rbac(me).permissionView(),
	defaultParams: { perPage: 20 },
	depends: 'permissions:list',
	title: 'Permissions'
});
