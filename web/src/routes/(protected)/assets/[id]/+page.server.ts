import { AssetService } from '$services/asset';
import type { AssetResource } from '$lib/resources/asset';

export const load = async ({ params, locals }) => {
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const modelService = new AssetService(authToken as string, currentOrgId);
	const model = (await modelService.findById(id, {
		include: ['account', 'accessGrants']
	})) as AssetResource;
	return {
		model,
		title: `Asset - #${model.data.attributes.id} - ${model.data.attributes.name}`
	};
};
