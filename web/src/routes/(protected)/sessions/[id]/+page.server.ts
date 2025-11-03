import { SessionService } from '$services/session';
import type { ApiSessionResource } from '$resources/session';
import type { Actions } from '@sveltejs/kit';
import { json } from '@sveltejs/kit';
import { fail } from 'sveltekit-superforms/client';
import { Rbac } from '$lib/rbac';

export const load = async ({ params, locals, depends }) => {
	depends('sessions:view');
	const { id } = params;
	const { authToken, currentOrgId, me } = locals;
	const rbac = new Rbac(me);
	rbac.sessionView();
	const modelService = new SessionService(authToken as string, currentOrgId as number);
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
		me,
		title: `Session - #${model.data.attributes.id} - ${model.data.attributes.id}`,
		canViewRequest: rbac.canRequestView()
	};
};

export const actions = {
	terminate: async ({ locals, params }) => {
		const { id } = params;
		const { authToken, currentOrgId, me } = locals;
		new Rbac(me).sessionTerminate();
		try {
			const sessionService = new SessionService(authToken as string, currentOrgId as number);
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
		const { authToken, currentOrgId, me } = locals;
		new Rbac(me).sessionCancel();
		try {
			const sessionService = new SessionService(authToken as string, currentOrgId as number);
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
		const { authToken, currentOrgId, me } = locals;
		new Rbac(me).sessionStart();
		try {
			const sessionService = new SessionService(authToken as string, currentOrgId as number);
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
		const { authToken, currentOrgId, me } = locals;
		new Rbac(me).sessionEnd();
		try {
			const sessionService = new SessionService(authToken as string, currentOrgId as number);
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
		const { authToken, currentOrgId, me } = locals;
		new Rbac(me).sessionRetrieveSecret();
		try {
			const sessionService = new SessionService(authToken as string, currentOrgId as number);
			const response = await sessionService.retrieveSecret(Number(id));
			return json({
				success: true,
				message: `Session secret retrieved successfully`,
				secret: response.data
			});
		} catch (error: any) {
			return fail(400, { error: `Failed to retrieve session secret` });
		}
	}
} satisfies Actions;
