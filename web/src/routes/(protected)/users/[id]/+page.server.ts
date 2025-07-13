import type { Actions, PageServerLoad } from './$types';
import { fail } from '@sveltejs/kit';
import { superValidate } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$utils/form';
import type { Role } from '$models/role';
import { RoleSchema } from '$validations/role';
import { RoleService } from '$services/role';
import { UserSchema } from '$lib/validations/user';
import { UserService } from '$lib/services/user';

export const load: PageServerLoad = async ({ depends, parent }) => {
	depends('user:show');
	const data = await parent();
	const form = await superValidate(data.model.data.attributes, zod(UserSchema));
	return {
		form,
		model: data.model,
		title: 'User'
	};
};

export const actions = {
	save: async ({ request, locals, params }) => {
		const { authToken, currentOrgId } = locals;
		const { id } = params;
		const form = await superValidate(request, zod(UserSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const userService = new UserService(authToken as string, currentOrgId);
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
	}
} satisfies Actions;
