import type { LayoutServerLoad } from './$types';
import { AssetService } from '$services/asset';
import type { ApiAssetResource } from '$resources/asset';
import type { Asset } from '$lib/models/asset';
import { superValidate } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { AssetUpdateSchema, AssetCredentialsSchema } from '$validations/asset';
import type { AssetAccountCollection, AssetAccountResource } from '$resources/asset-account';

export const load: LayoutServerLoad = async ({ params, locals }) => {
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const modelService = new AssetService(authToken as string, currentOrgId);
	const model = (await modelService.findById(id, {
		include: [
			'accounts',
			'approverUserGroups',
			'requesterUserGroups',
			'approverUsers',
			'requesterUsers'
		]
	})) as ApiAssetResource;
	const asset = model.data.attributes as Asset;
	const assetAccounts = model.data.relationships?.accounts as AssetAccountCollection;
	const adminAccount = assetAccounts.find(
		(account) => account.attributes.type === 'admin'
	) as AssetAccountResource;
	const editForm = await superValidate(
		{
			name: model.data.attributes.name,
			description: model.data.attributes.description,
			status: model.data.attributes.status
		},
		zod(AssetUpdateSchema)
	);
	const credentialsForm = await superValidate(
		{
			host: asset.host,
			port: asset.port,
			dbms: asset.dbms.toLowerCase(),
			// username: adminAccount.attributes.username,
			// password: 
		},
		zod(AssetCredentialsSchema)
	);
	return {
		model,
		asset,
		editForm,
		credentialsForm
	};
};
