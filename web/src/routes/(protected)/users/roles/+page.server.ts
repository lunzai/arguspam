import type { Actions, PageServerLoad } from './$types';
import { fail } from '@sveltejs/kit';
import { superValidate } from 'sveltekit-superforms';
import { zod4 } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$utils/form';
import type { Role } from '$models/role';
import { RoleSchema } from '$validations/role';
import { RoleService } from '$services/role';

export const load: PageServerLoad = async ({ depends }) => {
	depends('roles:list');
	const model = {
		name: '',
		description: '',
		is_default: false
	} as Role;
	const form = await superValidate(zod4(RoleSchema));
	return {
		form,
		model,
		title: 'Roles'
	};
};

export const actions = {
	save: async ({ request, locals }) => {
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod4(RoleSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const roleService = new RoleService(authToken as string, currentOrgId);
			const response = await roleService.create(data);
			return {
				success: true,
				message: `Role created successfully`,
				form: form,
				model: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to create role` });
		}
	}
} satisfies Actions;
