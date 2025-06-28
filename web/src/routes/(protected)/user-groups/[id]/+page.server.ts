import { UserGroupService } from '$services/user-group';
import type { UserGroupResource } from '$lib/resources/user-group';

export const load = async ({ params, locals }) => {
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const modelService = new UserGroupService(authToken as string, currentOrgId);
	const model = (await modelService.findById(id, {
		//include: ['account', 'accessGrants']
	})) as UserGroupResource;
	return {
		model,
		title: `User Group - #${model.data.attributes.id} - ${model.data.attributes.name}`
	};
};
