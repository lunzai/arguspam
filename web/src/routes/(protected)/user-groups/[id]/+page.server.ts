import { fail, redirect } from '@sveltejs/kit';
import type { Actions } from './$types';
import { UserGroupService } from '$services/user-group';
import type { UserGroupResource } from '$resources/user-group';
import { OrgService } from '$services/org';
import { UserGroupSchema } from '$validations/user-group';
import { superValidate } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$lib/utils/form';

export const load = async ({ params, locals, depends }) => {
	depends('user-groups:view');
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const userGroupService = new UserGroupService(authToken as string, currentOrgId);
	const model = (await userGroupService.findById(id, {
		include: ['users']
	})) as UserGroupResource;
	const orgService = new OrgService(authToken as string, currentOrgId);
	const userCollection = await orgService.getUsers(currentOrgId as number);
	const form = await superValidate(
		{
			org_id: Number(id),
			name: model.data.attributes.name,
			description: model.data.attributes.description,
			status: model.data.attributes.status
		},
		zod(UserGroupSchema)
	);
	return {
		form,
		model,
		userCollection,
		title: `User Group - #${model.data.attributes.id} - ${model.data.attributes.name}`
	};
};

export const actions = {
	save: async ({ request, locals, params }) => {
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod(UserGroupSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const userGroupService = new UserGroupService(authToken as string, currentOrgId);
			const response = await userGroupService.update(Number(id), data);
			return {
				success: true,
				message: `User group updated successfully`,
				form: form,
				userGroup: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to update user group` });
		}
	},
	delete: async ({ locals, params }) => {
		try {
			const { id } = params;
			const { authToken, currentOrgId } = locals;
			const userGroupService = new UserGroupService(authToken as string, currentOrgId);
			await userGroupService.delete(Number(id));
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
		redirect(302, '/user-groups');
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
			const userGroupService = new UserGroupService(authToken as string, currentOrgId);
			const response = await userGroupService.addUsers(Number(id), userIds);
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
			const userGroupService = new UserGroupService(authToken as string, currentOrgId);
			await userGroupService.deleteUser(Number(id), userIds.split(','));
			return;
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
	}
} satisfies Actions;
