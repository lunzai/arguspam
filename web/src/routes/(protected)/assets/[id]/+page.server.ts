import type { PageServerLoad } from './$types';
import { fail, redirect } from '@sveltejs/kit';
import type { Actions } from './$types';
import { AssetService } from '$services/asset';
import { zod } from 'sveltekit-superforms/adapters';
import { superValidate } from 'sveltekit-superforms';
import { setFormErrors } from '$lib/utils/form';
import {
	AssetUpdateSchema,
	AssetCredentialsSchema,
	AssetRemoveAccessSchema
} from '$validations/asset';
import type { Asset } from '$models/asset';
import type { AssetAccount } from '$models/asset-account';
import type { AssetAccountCollection, AssetAccountResource } from '$resources/asset-account';
import type { ApiAssetResource } from '$resources/asset';

export const load: PageServerLoad = async ({ params, locals, parent, depends }) => {
	depends('asset:view');
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const { model, asset } = await parent();

	return {
		model,
		title: `Asset - #${asset.id} - ${asset.name}`
	};
};

export const actions = {
	testConnection: async ({ request, locals, params }) => {
		try {
			const { id } = params;
			const { authToken, currentOrgId } = locals;
			const assetService = new AssetService(authToken as string, currentOrgId);
			const response = await assetService.testConnection(Number(id));
			return {
				success: true,
				message: 'Connection test successful'
			};
		} catch (error) {
			return fail(400, { error: 'Connection failed' });
		}
	},
	delete: async ({ locals, params }) => {
		try {
			const { id } = params;
			const { authToken, currentOrgId } = locals;
			const assetService = new AssetService(authToken as string, currentOrgId);
			await assetService.delete(Number(id));
		} catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
		redirect(302, '/assets');
	},
	save: async ({ request, locals, params }) => {
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod(AssetUpdateSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const assetService = new AssetService(authToken as string, currentOrgId);
			const response = await assetService.update(Number(id), data);
			return {
				success: true,
				message: `Asset updated successfully`,
				form: form,
				asset: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to update asset` });
		}
	},
	updateCredentials: async ({ request, locals, params }) => {
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod(AssetCredentialsSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const assetService = new AssetService(authToken as string, currentOrgId);
			const response = (await assetService.updateCredentials(Number(id), data)) as ApiAssetResource;
			return {
				success: true,
				message: `Asset credentials updated successfully`,
				form: form,
				asset: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to update asset credentials` });
		}
	},
	removeAccess: async ({ request, locals, params }) => {
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod(AssetRemoveAccessSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const assetService = new AssetService(authToken as string, currentOrgId);
		try {
			await assetService.removeUserOrGroup(
				Number(id),
				form.data.role,
				form.data.id,
				form.data.type
			);
			return {
				success: true,
				message: `Access removed successfully`,
				form: form
			};
		} catch (error: any) {
			return fail(400, { form, error: `Failed to remove access` });
		}
	},
	addUsers: async ({ request, locals, params }) => {
		// try {
		// 	const { id } = params;
		// 	const { authToken, currentOrgId } = locals;
		// 	const data = await request.formData();
		// 	const userIds = data.get('userIds')?.toString().split(',') ?? [];
		// 	if (userIds.length === 0) {
		// 		return fail(400, {
		// 			message: 'No users selected'
		// 		});
		// 	}
		// 	const userGroupService = new UserGroupService(authToken as string, currentOrgId);
		// 	const response = await userGroupService.addUsers(Number(id), userIds);
		// 	return;
		// } catch (error) {
		// 	return fail(400, {
		// 		message: error instanceof Error ? error.message : 'Unknown error'
		// 	});
		// }
	},
	deleteUser: async ({ request, locals, params }) => {
		// try {
		// 	const { id } = params;
		// 	const { authToken, currentOrgId } = locals;
		// 	const data = await request.formData();
		// 	const userIds = data.get('userIds')?.toString() ?? '';
		// 	const userGroupService = new UserGroupService(authToken as string, currentOrgId);
		// 	await userGroupService.deleteUser(Number(id), userIds.split(','));
		// 	return;
		// } catch (error) {
		// 	return fail(400, {
		// 		message: error instanceof Error ? error.message : 'Unknown error'
		// 	});
		// }
	}
} satisfies Actions;
