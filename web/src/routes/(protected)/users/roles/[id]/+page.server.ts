import { RoleService } from '$services/role';
import type { ApiRoleResource } from '$resources/role';
import { PermissionService } from '$services/permission';
import { zod4 } from 'sveltekit-superforms/adapters';
import { superValidate } from 'sveltekit-superforms';
import { RoleSchema } from '$validations/role';
import { fail } from '@sveltejs/kit';
import { setFormErrors } from '$utils/form';
import type { Actions } from '@sveltejs/kit';
import { redirect } from '@sveltejs/kit';
import { Rbac } from '$lib/rbac';

export const load = async ({ params, locals, depends }) => {
	depends('roles:view');
	const { id } = params;
	const { authToken, currentOrgId, me } = locals;
	new Rbac(me).roleView();
	const modelService = new RoleService(authToken as string, currentOrgId as number);
	const model = (await modelService.findById(id, {
		include: ['permissions', 'users']
		// count: ['users'],
	})) as ApiRoleResource;
	const rolePermissionCollection = await modelService.getPermissions(Number(id) as number);
	const permissionService = new PermissionService(authToken as string, currentOrgId as number);
	const permissionCollection = await permissionService.findAll({
		perPage: 10000
		// sort: ['name']
	});
	const form = await superValidate(
		{
			name: model.data.attributes.name,
			description: model.data.attributes.description,
			is_default: model.data.attributes.is_default
		},
		zod4(RoleSchema)
	);
	return {
		form,
		model,
		permissionCollection,
		rolePermissionCollection,
		title: `Role - #${model.data.attributes.id} - ${model.data.attributes.name}`
	};
};

export const actions = {
	save: async ({ request, locals, params }) => {
		const { id } = params;
		const { authToken, currentOrgId, me } = locals;
		new Rbac(me).roleUpdate();
		const form = await superValidate(request, zod4(RoleSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const roleService = new RoleService(authToken as string, currentOrgId as number);
			const response = await roleService.update(Number(id), data);
			return {
				success: true,
				message: `Role updated successfully`,
				form: form,
				role: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to update role` });
		}
	},
	delete: async ({ locals, params }) => {
		const { me } = locals;
		new Rbac(me).roleDelete();
		try {
			const { id } = params;
			const { authToken, currentOrgId } = locals;
			const roleService = new RoleService(authToken as string, currentOrgId as number);
			await roleService.delete(Number(id));
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
		redirect(302, '/users/roles');
	},
	permissions: async ({ request, locals, params }) => {
		const { authToken, currentOrgId, me } = locals;
		new Rbac(me).roleUpdatePermissions();
		try {
			const { id } = params;
			const data = await request.formData();
			const permissionIds = data.get('permissionIds')?.toString().split(',').map(Number) ?? [];
			const roleService = new RoleService(authToken as string, currentOrgId as number);
			const response = await roleService.syncPermissions(Number(id), permissionIds);
			return;
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
	}
} satisfies Actions;
