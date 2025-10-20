import { SessionService } from '$services/session';
import type { ApiSessionResource } from '$resources/session';
import type { Actions } from '@sveltejs/kit';
import { fail } from 'sveltekit-superforms/client';

export const load = async ({ params, locals, depends }) => {
	depends('sessions:view');
	const { id } = params;
	const { authToken, currentOrgId, user } = locals;
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
        user,
		title: `Session - #${model.data.attributes.id} - ${model.data.attributes.id}`
	};
};

export const actions = {
	terminate: async ({ locals, params }) => {
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		try {
			const sessionService = new SessionService(authToken as string, currentOrgId);
			const response = await sessionService.terminate(Number(id));
			return {
				success: true,
				message: `Session terminated successfully`,
				model: response.data.attributes
			};
		} catch (error: any) {
			return fail(400, { error: `Failed to terminate session` });
		}
	},
    cancel: async ({ locals, params }) => {
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		try {
			const sessionService = new SessionService(authToken as string, currentOrgId);
			const response = await sessionService.cancel(Number(id));
			return {
				success: true,
				message: `Session cancelled successfully`,
				model: response.data.attributes
			};
		} catch (error: any) {
			return fail(400, { error: `Failed to cancel session` });
		}
	},
    start: async ({ locals, params }) => {
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		try {
			const sessionService = new SessionService(authToken as string, currentOrgId);
			const response = await sessionService.start(Number(id));
			return {
				success: true,
				message: `Session started successfully`,
				model: response.data.attributes
			};
		} catch (error: any) {
			return fail(400, { error: `Failed to start session` });
		}
	},
    end: async ({ locals, params }) => {
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		try {
			const sessionService = new SessionService(authToken as string, currentOrgId);
			const response = await sessionService.end(Number(id));
			return {
				success: true,
				message: `Session ended successfully`,
				model: response.data.attributes
			};
		} catch (error: any) {
			return fail(400, { error: `Failed to end session` });
		}
	},
    secret: async ({ locals, params }) => {
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		try {
			const sessionService = new SessionService(authToken as string, currentOrgId);
			const response = await sessionService.retrieveSecret(Number(id));
			return {
				success: true,
				message: `Session secret retrieved successfully`,
				model: response.data.attributes
			};
		} catch (error: any) {
			return fail(400, { error: `Failed to retrieve session secret` });
		}
	}
} satisfies Actions;
