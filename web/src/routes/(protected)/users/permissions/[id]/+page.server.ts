import { PermissionService } from '$services/permission';
import type { ApiPermissionResource } from '$lib/resources/permission';
import { Rbac } from '$lib/rbac';

export const load = async ({ params, locals }) => {
	const { id } = params;
	const { authToken, currentOrgId, me } = locals;
	new Rbac(me).permissionView();
	const modelService = new PermissionService(authToken as string, currentOrgId as number);
	const model = (await modelService.findById(id, {
		// include: ['account', 'accessGrants']
	})) as ApiPermissionResource;
	return {
		model,
		title: `Permission - #${model.data.attributes.id} - ${model.data.attributes.name}`
	};
};
