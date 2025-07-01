import { RoleService } from '$services/role';
import type { RoleResource } from '$lib/resources/role';

export const load = async ({ params, locals }) => {
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const modelService = new RoleService(authToken as string, currentOrgId);
	const model = (await modelService.findById(id, {
		// include: ['account', 'accessGrants']
	})) as RoleResource;
	return {
		model,
		title: `Role - #${model.data.attributes.id} - ${model.data.attributes.name}`
	};
};
