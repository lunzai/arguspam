import type { PageServerLoad } from './$types';
import { Rbac } from '$lib/rbac';
import { SessionService } from '$lib/services/session';
import { mergeParams } from '$components/datatable';
import type { ApiMeta } from '$lib/resources/api';

export const load: PageServerLoad = async ({ depends, locals, url }) => {
	depends('sessions:list');
	const { authToken, currentOrgId, me } = locals;
	new Rbac(me).sessionView();
	const modelService = new SessionService(authToken as string, currentOrgId as number);
	const response = await modelService.findAll(
		mergeParams(
			{
				perPage: 20,
				include: ['asset', 'requester', 'approver', 'request'],
				sort: ['-created_at']
			},
			url
		)
	);
	return {
		title: 'Sessions',
		list: response.data,
		meta: response.meta as ApiMeta
	};
};
