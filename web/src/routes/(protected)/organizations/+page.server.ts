import type { Actions, PageServerLoad } from './$types';
import { fail } from '@sveltejs/kit';
import { OrgService } from '$services/org';
import { superValidate } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$utils/form';
import type { Org } from '$models/org';
import { OrgSchema } from '$validations/org';

export const load: PageServerLoad = async ({ locals, depends }) => {
	depends('organizations:list');
	const model = {
		name: '',
		description: '',
		status: 'active'
	} as Org;
	const form = await superValidate(zod(OrgSchema));
	return {
		form,
		model,
		title: 'Organizations'
	};
};

export const actions = {
	save: async ({ request, locals, params }) => {
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod(OrgSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const orgService = new OrgService(authToken as string, currentOrgId);
			const response = await orgService.create(data);
			return {
				success: true,
				message: `Organization created successfully`,
				form: form,
				model: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to create organization` });
		}
	}
} satisfies Actions;
