import { RequestService } from '$services/request';
import type { RequestResource } from '$lib/resources/request';

export const load = async ({ params, locals }) => {
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const modelService = new RequestService(authToken as string, currentOrgId);
	const model = (await modelService.findById(id, {
		include: ['account', 'accessGrants']
	})) as RequestResource;
	return {
		model,
		title: `Request - #${model.data.attributes.id} - ${model.data.attributes.id}`
	};
};
