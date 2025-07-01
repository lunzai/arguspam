import { SessionService } from '$services/session';
import type { SessionResource } from '$lib/resources/session';

export const load = async ({ params, locals }) => {
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const modelService = new SessionService(authToken as string, currentOrgId);
	const model = (await modelService.findById(id, {
		// include: ['account', 'accessGrants']
	})) as SessionResource;
	return {
		model,
		title: `Session - #${model.data.attributes.id} - ${model.data.attributes.id}`
	};
};
