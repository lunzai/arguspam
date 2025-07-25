import type { PageServerLoad, Actions } from './$types';
import { superValidate } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$utils/form';
import { fail } from '@sveltejs/kit';
import { AssetSchema } from '$lib/validations/asset';
import { AssetService } from '$lib/services/asset';
import type { Asset } from '$lib/models/asset';

export const load: PageServerLoad = async ({ locals, depends }) => {
	depends('assets:list');
    const { currentOrgId } = locals;
	const model = {
		org_id: Number(currentOrgId),
		name: '',
		description: '',
		status: 'active',
		host: '',
		port: null,
		dbms: '',
		username: '',
		password: ''
	} as Asset;
	const form = await superValidate(zod(AssetSchema));
	return {
		title: 'Assets',
		form,
		model
	};
};

export const actions = {
	save: async ({ request, locals, params }) => {
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod(AssetSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const assetService = new AssetService(authToken as string, currentOrgId);
			const response = await assetService.create(data);
			return {
				success: true,
				message: `Asset created successfully`,
				form: form,
				model: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to create asset` });
		}
	}
} satisfies Actions;
