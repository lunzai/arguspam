import { RoleService } from '$services/role';
import type { RoleResource } from '$resources/role';
import { PermissionService } from '$services/permission';
import { zod } from 'sveltekit-superforms/adapters';
import { superValidate } from 'sveltekit-superforms';
import { RoleSchema } from '$validations/role';
import { fail } from '@sveltejs/kit';
import { setFormErrors } from '$utils/form';
import type { Actions } from '@sveltejs/kit';
import { redirect } from '@sveltejs/kit';

export const load = async ({ params, locals, depends }) => {
	depends('roles:view');
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const modelService = new RoleService(authToken as string, currentOrgId);
	const model = (await modelService.findById(id, {
		include: ['permissions', 'users']
		// count: ['users'],
	})) as RoleResource;
	const rolePermissionCollection = await modelService.getPermissions(Number(id));
	const permissionService = new PermissionService(authToken as string, currentOrgId);
	const permissionCollection = await permissionService.findAll({
		perPage: 1000,
		sort: ['name']
	});
	const form = await superValidate(
		{
			name: model.data.attributes.name,
			description: model.data.attributes.description,
			is_default: model.data.attributes.is_default
		},
		zod(RoleSchema)
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
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod(RoleSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const roleService = new RoleService(authToken as string, currentOrgId);
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
		try {
			const { id } = params;
			const { authToken, currentOrgId } = locals;
			const roleService = new RoleService(authToken as string, currentOrgId);
			await roleService.delete(Number(id));
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
		redirect(302, '/roles');
	},
	permissions: async ({ request, locals, params }) => {
		try {
			const { id } = params;
			const { authToken, currentOrgId } = locals;
			const data = await request.formData();
			const permissionIds = data.get('permissionIds')?.toString().split(',').map(Number) ?? [];
			const roleService = new RoleService(authToken as string, currentOrgId);
			const response = await roleService.syncPermissions(Number(id), permissionIds);
			console.log('RESPONSE', response);
			return;
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
	}
} satisfies Actions;
