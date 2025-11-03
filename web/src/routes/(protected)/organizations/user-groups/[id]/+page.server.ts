import { fail, redirect } from '@sveltejs/kit';
import type { Actions } from './$types';
import { UserGroupService } from '$services/user-group';
import type { ApiUserGroupResource } from '$resources/user-group';
import { OrgService } from '$services/org';
import { UserGroupSchema } from '$validations/user-group';
import { superValidate } from 'sveltekit-superforms';
import { zod4 } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$utils/form';
import { Rbac } from '$lib/rbac';

export const load = async ({ params, locals, depends }) => {
	depends('user-groups:view');
	const rbac = new Rbac(locals.me);
	rbac.userGroupView();
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const userGroupService = new UserGroupService(authToken as string, currentOrgId as number);
	const model = (await userGroupService.findById(id, {
		include: ['users']
	})) as ApiUserGroupResource;
	const orgService = new OrgService(authToken as string, currentOrgId as number);
	const userCollection = await orgService.getUsers(currentOrgId as number);
	const form = await superValidate(
		{
			org_id: Number(id),
			name: model.data.attributes.name,
			description: model.data.attributes.description,
			status: model.data.attributes.status
		},
		zod4(UserGroupSchema)
	);
	return {
		form,
		model,
		userCollection,
		title: `User Group - #${model.data.attributes.id} - ${model.data.attributes.name}`,
		canUpdate: rbac.canUserGroupUpdate(),
		canDelete: rbac.canUserGroupDelete(),
		canAddUser: rbac.canUserGroupAddUser(),
		canRemoveUser: rbac.canUserGroupRemoveUser()
	};
};

export const actions = {
	save: async ({ request, locals, params }) => {
		new Rbac(locals.me).userGroupUpdate();
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod4(UserGroupSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const userGroupService = new UserGroupService(authToken as string, currentOrgId as number);
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
		new Rbac(locals.me).userGroupDelete();
		try {
			const { id } = params;
			const { authToken, currentOrgId } = locals;
			const userGroupService = new UserGroupService(authToken as string, currentOrgId as number);
			await userGroupService.delete(Number(id));
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
		redirect(302, '/organizations/user-groups');
	},
	addUsers: async ({ request, locals, params }) => {
		new Rbac(locals.me).userGroupAddUser();
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
			const userGroupService = new UserGroupService(authToken as string, currentOrgId as number);
			const response = await userGroupService.addUsers(Number(id), userIds);
			return;
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
	},
	deleteUser: async ({ request, locals, params }) => {
		new Rbac(locals.me).userGroupRemoveUser();
		try {
			const { id } = params;
			const { authToken, currentOrgId } = locals;
			const data = await request.formData();
			const userIds = data.get('userIds')?.toString() ?? '';
			const userGroupService = new UserGroupService(authToken as string, currentOrgId as number);
			await userGroupService.deleteUser(Number(id), userIds.split(','));
			return;
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
	}
} satisfies Actions;
