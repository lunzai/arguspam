import type { PageServerLoad, Actions } from './$types';
import { superValidate } from 'sveltekit-superforms';
import { zod4 } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$utils/form';
import { fail } from '@sveltejs/kit';
import { AssetSchema } from '$lib/validations/asset';
import { AssetService as ModelService } from '$lib/services/asset';
import type { AssetCreateRequest } from '$lib/models/asset';
import { Rbac } from '$lib/rbac';
import { mergeParams } from '$components/datatable';
import type { ApiMeta } from '$lib/resources/api';

export const load: PageServerLoad = async ({ locals, depends, url }) => {
	depends('assets:list');
	const { authToken, currentOrgId, me } = locals;
	new Rbac(me).assetView();
	const model: Partial<AssetCreateRequest> = {
		org_id: Number(currentOrgId),
		status: 'active'
	};
	const form = await superValidate(zod4(AssetSchema));
	const modelService = new ModelService(authToken as string, currentOrgId as number);
	const response = await modelService.findAll(
		mergeParams(
			{
				perPage: 20,
				sort: ['-created_at']
			},
			url
		)
	);
	return {
		title: 'Assets',
		list: response.data,
		meta: response.meta as ApiMeta,
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
			const assetService = new ModelService(authToken as string, currentOrgId as number);
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
