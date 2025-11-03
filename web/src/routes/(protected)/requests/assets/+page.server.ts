import { RequestService } from '$services/request';
import { UserService } from '$services/user';
import type { ApiRequestResource } from '$resources/request';
import type { ApiAssetCollection } from '$lib/resources/asset.js';
import { superValidate } from 'sveltekit-superforms';
import { zod4 } from 'sveltekit-superforms/adapters';
import { RequesterSchema } from '$lib/validations/request';
import type { Actions } from '@sveltejs/kit';
import { fail } from '@sveltejs/kit';
import { setFormErrors } from '$lib/utils/form';
import { Rbac } from '$lib/rbac';

export const load = async ({ params, locals, depends }) => {
	depends('requests:create');
	new Rbac(locals.me).assetViewRequestable();
	const { authToken, currentOrgId } = locals;
	const userService = new UserService(authToken as string, currentOrgId as number);
	const assetCollection = (await userService.getRequesterAssets()) as ApiAssetCollection;
	const form = await superValidate(zod4(RequesterSchema));
	return {
		assetCollection,
		title: `Assets`,
		form
	};
};

export const actions = {
	default: async ({ request, locals, params }) => {
		new Rbac(locals.me).requestCreate();
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod4(RequesterSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const requestService = new RequestService(authToken as string, currentOrgId as number);
			const response = await requestService.create(data);
			return {
				success: true,
				message: `Request submitted successfully`,
				form: form,
				model: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to submit request` });
		}
	}
} satisfies Actions;
