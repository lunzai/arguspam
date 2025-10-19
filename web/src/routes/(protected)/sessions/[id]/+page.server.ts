import { SessionService } from '$services/session';
import type { ApiSessionResource } from '$resources/session';

export const load = async ({ params, locals, depends }) => {
	depends('sessions:view');
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const modelService = new SessionService(authToken as string, currentOrgId);
	const model = (await modelService.findById(id, {
		include: [
			'request',
			'asset',
			'requester',
			'approver',
			'cancelledBy',
			'terminatedBy',
			'flags',
			'audits'
		]
	})) as ApiSessionResource;
	const permissions = await modelService.permissions(Number(id));
	return {
		model,
		permissions,
		title: `Session - #${model.data.attributes.id} - ${model.data.attributes.id}`
	};
};
