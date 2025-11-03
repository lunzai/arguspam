import type { Actions, PageServerLoad } from './$types';
import { fail } from '@sveltejs/kit';
import { OrgService } from '$services/org';
import { superValidate } from 'sveltekit-superforms';
import { zod4 } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$utils/form';
import type { Org } from '$models/org';
import { OrgSchema } from '$validations/org';
import { Rbac } from '$lib/rbac';

export const load: PageServerLoad = async ({ locals, depends }) => {
	depends('organizations:list');
	const rbac = new Rbac(locals.me);
	rbac.orgView();
	const model = {
		name: '',
		description: '',
		status: 'active'
	} as Org;
	const form = await superValidate(zod4(OrgSchema));
	return {
		form,
		model,
		title: 'Organizations',
		canCreate: rbac.canOrgCreate()
	};
};

export const actions = {
	save: async ({ request, locals, params }) => {
		new Rbac(locals.me).orgCreate();
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod4(OrgSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const orgService = new OrgService(authToken as string, currentOrgId as number);
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
