import { RequestService } from '$services/request';
import type { ApiRequestResource } from '$resources/request';
import type { Actions } from '@sveltejs/kit';
import { fail, superValidate } from 'sveltekit-superforms/client';
import { ApproveSchema, RejectSchema } from '$lib/validations/request';
import { zod4 } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$utils/form';
import { Rbac } from '$lib/rbac';

export const load = async ({ params, locals, depends }) => {
	depends('requests:view');
	const rbac = new Rbac(locals.me);
	rbac.requestView();
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const modelService = new RequestService(authToken as string, currentOrgId as number);
	const model = (await modelService.findById(id, {
		include: ['account', 'accessGrants', 'asset', 'requester', 'approver', 'rejecter', 'session']
	})) as ApiRequestResource;
	const permissions = await modelService.permission(Number(id));
	const approveForm = await superValidate(
		{
			start_datetime: new Date(model.data.attributes.start_datetime),
			end_datetime: new Date(model.data.attributes.end_datetime),
			duration: model.data.attributes.duration,
			scope: model.data.attributes.scope,
			approver_risk_rating: model.data.attributes.ai_risk_rating,
			approver_note: model.data.attributes.approver_note
		},
		zod4(ApproveSchema),
		{ errors: false }
	);
	const rejectForm = await superValidate(zod4(RejectSchema));
	return {
		approveForm,
		rejectForm,
		model,
		permissions,
		canViewSession: rbac.canSessionView(),
		title: `Request - #${model.data.attributes.id} - ${model.data.attributes.id}`
	};
};

export const actions = {
	approve: async ({ request, locals, params }) => {
		new Rbac(locals.me).requestApprove();
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod4(ApproveSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const requestService = new RequestService(authToken as string, currentOrgId as number);
			const response = await requestService.approve(Number(id), data);
			return {
				success: true,
				message: `Request approved successfully`,
				form: form,
				model: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(422, { form });
			}
			return fail(400, { form, error: `Failed to approve request` });
		}
	},
	reject: async ({ request, locals, params }) => {
		new Rbac(locals.me).requestReject();
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod4(RejectSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const requestService = new RequestService(authToken as string, currentOrgId as number);
			const response = await requestService.reject(Number(id), data);
			return {
				success: true,
				message: `Request rejected successfully`,
				form: form,
				model: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(422, { form });
			}
			return fail(400, { form, error: `Failed to reject request` });
		}
	},
	cancel: async ({ locals, params }) => {
		new Rbac(locals.me).requestCancel();
		try {
			const { id } = params;
			const { authToken, currentOrgId } = locals;
			const requestService = new RequestService(authToken as string, currentOrgId as number);
			await requestService.cancel(Number(id));
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
	}
} satisfies Actions;
