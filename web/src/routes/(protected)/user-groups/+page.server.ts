import type { Actions, PageServerLoad } from './$types';
import { fail } from '@sveltejs/kit';
import { UserGroupService } from '$services/user-group';
import { superValidate } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$utils/form';
import type { UserGroup } from '$models/user-group';
import { UserGroupSchema } from '$validations/user-group';

export const load: PageServerLoad = async ({ locals, depends }) => {
	depends('user-groups:list');
	const { currentOrgId } = locals;
	const model = {
		org_id: Number(currentOrgId),
		name: '',
		description: '',
		status: 'active'
	} as UserGroup;
	const form = await superValidate(zod(UserGroupSchema));
	return {
		form,
		model,
		title: 'User Groups'
	};
};

export const actions = {
	save: async ({ request, locals, params }) => {
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod(UserGroupSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const userGroupService = new UserGroupService(authToken as string, currentOrgId);
			const response = await userGroupService.create(data);
			return {
				success: true,
				message: `User group created successfully`,
				form: form,
				model: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to create user group` });
		}
	}
} satisfies Actions;
