import type { PageServerLoad } from './$types';
import { Rbac } from '$lib/rbac';
import { UserService } from '$lib/services/user';
import { mergeParams } from '$components/datatable';
import type { ApiMeta } from '$lib/resources/api';

export const load: PageServerLoad = async ({ depends, locals, url }) => {
	depends('users:list');
	const { authToken, currentOrgId, me } = locals;
	new Rbac(me).userViewAny();
	const modelService = new UserService(authToken as string, currentOrgId as number);
	const response = await modelService.findAll(
		mergeParams(
			{
				perPage: 20,
				include: ['roles'],
				filter: { status: 'active' }
			},
			url
		)
	);
	return {
		title: 'Users',
		list: response.data,
		meta: response.meta as ApiMeta
	};
};
