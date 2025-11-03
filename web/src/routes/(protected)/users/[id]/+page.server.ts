import type { Actions, PageServerLoad } from './$types';
import { fail } from '@sveltejs/kit';
import { superValidate } from 'sveltekit-superforms';
import { zod4 } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$utils/form';
import { RoleService } from '$services/role';
import { UserSchema, UserUpdateRolesSchema } from '$validations/user';
import { UserService } from '$services/user';
import { Rbac } from '$lib/rbac';
import type { RoleResource } from '$resources/role';

export const load: PageServerLoad = async ({ depends, parent, locals }) => {
	depends('user:view');
	const { authToken, currentOrgId, me } = locals;
	new Rbac(me).userViewAny();
	const data = await parent();
	const roles = await new RoleService(authToken as string, currentOrgId as number).findAll({
		perPage: 1000,
		filter: {
			status: 'active'
		}
	});
	const form = await superValidate(data.model.data.attributes, zod4(UserSchema));
	const updateRolesForm = await superValidate(
		{
			roleIds:
				data.model.data.relationships?.roles?.map((role: RoleResource) =>
					role.attributes.id.toString()
				) ?? []
		},
		zod4(UserUpdateRolesSchema)
	);
	return {
		form,
		updateRolesForm,
		model: data.model,
		roles: roles.data,
		title: 'User'
	};
};

export const actions = {
	save: async ({ request, locals, params }) => {
		const { authToken, currentOrgId, me } = locals;
		new Rbac(me).userUpdateAny();
		const { id } = params;
		const form = await superValidate(request, zod4(UserSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const userService = new UserService(authToken as string, currentOrgId as number);
			const response = await userService.update(id, data);
			return {
				success: true,
				message: `User updated successfully`,
				form: form,
				model: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to update user` });
		}
	},
	roles: async ({ request, locals, params }) => {
		const { authToken, currentOrgId, me } = locals;
		new Rbac(me).userUpdateAny();
		const { id } = params;
		const form = await superValidate(request, zod4(UserUpdateRolesSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		try {
			const data = form.data;
			const roleIds = data.roleIds.map((roleId: string) => parseInt(roleId));
			const userService = new UserService(authToken as string, currentOrgId as number);
			const response = await userService.updateRoles(parseInt(id), roleIds);
			return {
				success: true,
				message: `User roles updated successfully`,
				form: form
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to update user roles` });
		}
	}
} satisfies Actions;
