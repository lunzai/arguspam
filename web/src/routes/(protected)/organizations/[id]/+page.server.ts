import { OrgService } from '$services/org';
import type { OrgResource } from '$lib/resources/org';

export const load = async ({ params, locals }) => {
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const modelService = new OrgService(authToken as string, currentOrgId);
	const model = (await modelService.findById(id, {
		// include: ['account', 'accessGrants']
	})) as OrgResource;
	return {
		model,
		title: `Organization - #${model.data.attributes.id} - ${model.data.attributes.name}`
	};
};
