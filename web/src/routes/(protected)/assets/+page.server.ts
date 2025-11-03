import type { PageServerLoad, Actions } from './$types';
import { superValidate } from 'sveltekit-superforms';
import { zod4 } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$utils/form';
import { error, fail } from '@sveltejs/kit';
import { AssetSchema } from '$lib/validations/asset';
import { AssetService } from '$lib/services/asset';
import type { AssetCreateRequest } from '$lib/models/asset';
import { Rbac } from '$lib/rbac';

export const load: PageServerLoad = async ({ locals, depends }) => {
	depends('assets:list');
	new Rbac(locals.me).assetView();
	const { currentOrgId } = locals;
	const model: Partial<AssetCreateRequest> = {
		org_id: Number(currentOrgId),
		status: 'active'
	};
	const form = await superValidate(zod4(AssetSchema));
	return {
		title: 'Assets',
		form,
		model
	};
};

export const actions = {
	save: async ({ request, locals, params }) => {
		new Rbac(locals.me).assetCreate();
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod4(AssetSchema), { errors: false });
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const assetService = new AssetService(authToken as string, currentOrgId as number);
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
