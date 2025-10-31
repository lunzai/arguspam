import type { PageServerLoad } from './$types';
import { fail, redirect } from '@sveltejs/kit';
import type { Actions } from './$types';
import { AssetService } from '$services/asset';
import { OrgService } from '$services/org';
import { zod4 } from 'sveltekit-superforms/adapters';
import { superValidate } from 'sveltekit-superforms';
import { setFormErrors } from '$lib/utils/form';
import {
	AssetUpdateSchema,
	AssetCredentialsSchema,
	AssetRemoveAccessSchema,
	AssetAddAccessSchema
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

	const orgService = new OrgService(authToken as string, currentOrgId);
	const userCollection = await orgService.getUsers(currentOrgId as number);
	const userGroupCollection = await orgService.getUserGroups(currentOrgId as number);
	return {
		model,
		userCollection,
		userGroupCollection,
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
		const form = await superValidate(request, zod4(AssetUpdateSchema));
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
		const form = await superValidate(request, zod4(AssetCredentialsSchema));
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
		const form = await superValidate(request, zod4(AssetRemoveAccessSchema));
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
	addAccess: async ({ request, locals, params }) => {
		const { id } = params;
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod4(AssetAddAccessSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const assetService = new AssetService(authToken as string, currentOrgId);
		try {
			await assetService.addUserOrGroup(
				Number(id),
				form.data.role,
				form.data.userIds,
				form.data.groupIds
			);
			return {
				success: true,
				message: `Access granted successfully`,
				form: form
			};
		} catch (error: any) {
			return fail(400, { form, error: `Failed to grant access` });
		}
	}
} satisfies Actions;
