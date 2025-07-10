// import { OrgService } from '$services/org';
// import type { OrgResource } from '$lib/resources/org';

// export const load = async ({ params, locals }) => {
// 	const { id } = params;
// 	const { authToken, currentOrgId } = locals;
// 	const modelService = new OrgService(authToken as string, currentOrgId);
// 	const model = (await modelService.findById(id, {
// 		// include: ['account', 'accessGrants']
// 	})) as OrgResource;
// 	return {
// 		model,
// 		title: `Organization - #${model.data.attributes.id} - ${model.data.attributes.name}`
// 	};
// };

import { fail, redirect } from '@sveltejs/kit';
import type { Actions } from './$types';
import { OrgService } from '$services/org';
import type { OrgResource } from '$resources/org';
import { OrgSchema } from '$validations/org';
import { superValidate } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$lib/utils/form';

export const load = async ({ params, locals, depends }) => {
	depends('organizations:view');
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const orgService = new OrgService(authToken as string, currentOrgId);
	const model = (await orgService.findById(id, {
		include: ['users']
	})) as OrgResource;
	const userCollection = await orgService.getUsers(currentOrgId as number);
	const form = await superValidate(
		{
			name: model.data.attributes.name,
			description: model.data.attributes.description,
			status: model.data.attributes.status
		},
		zod(OrgSchema)
	);
	return {
		form,
		model,
		userCollection,
		title: `Organization - #${model.data.attributes.id} - ${model.data.attributes.name}`
	};
};

export const actions = {
	save: async ({ request, locals, params }) => {
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod(OrgSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const orgService = new OrgService(authToken as string, currentOrgId);
			const response = await orgService.update(Number(id), data);
			return {
				success: true,
				message: `Organization updated successfully`,
				form: form,
				org: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to update organization` });
		}
	},
	delete: async ({ locals, params }) => {
		try {
			const { id } = params;
			const { authToken, currentOrgId } = locals;
			const orgService = new OrgService(authToken as string, currentOrgId);
			await orgService.delete(Number(id));
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
		redirect(302, '/organizations');
	},
	addUsers: async ({ request, locals, params }) => {
		try {
			const { id } = params;
			const { authToken, currentOrgId } = locals;
			const data = await request.formData();
			const userIds = data.get('userIds')?.toString().split(',') ?? [];
			if (userIds.length === 0) {
				return fail(400, {
					message: 'No users selected'
				});
			}
			const orgService = new OrgService(authToken as string, currentOrgId);
			const response = await orgService.addUsers(Number(id), userIds);
			console.log('RESPONSE', response);
			return;
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
	},
	deleteUser: async ({ request, locals, params }) => {
		try {
			const { id } = params;
			const { authToken, currentOrgId } = locals;
			const data = await request.formData();
			const userIds = data.get('userIds')?.toString() ?? '';
			const orgService = new OrgService(authToken as string, currentOrgId);
			await orgService.deleteUser(Number(id), userIds.split(','));
			return;
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
	}
} satisfies Actions;
