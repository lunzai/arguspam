import type { LayoutServerLoad } from './$types';
import { AssetService } from '$services/asset';
import type { ApiAssetResource } from '$resources/asset';
import type { Asset } from '$lib/models/asset';
import { superValidate } from 'sveltekit-superforms';
import { zod4 } from 'sveltekit-superforms/adapters';
import { AssetUpdateSchema, AssetCredentialsSchema } from '$validations/asset';
import type { AssetAccountCollection, AssetAccountResource } from '$resources/asset-account';
import { Rbac } from '$lib/rbac';

export const load: LayoutServerLoad = async ({ params, locals, depends }) => {
	depends('asset:view');
	const rbac = new Rbac(locals.me);
	rbac.assetView();
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const modelService = new AssetService(authToken as string, currentOrgId as number);
	const model = (await modelService.findById(id, {
		include: [
			'activeAccounts',
			'approverUserGroups',
			'requesterUserGroups',
			'approverUsers',
			'requesterUsers'
		]
	})) as ApiAssetResource;
	const asset = model.data.attributes as Asset;
	const assetAccounts = model.data.relationships?.activeAccounts as AssetAccountCollection;
	const adminAccount = assetAccounts.find(
		(account) => account.attributes.type === 'admin'
	) as AssetAccountResource;
	const editForm = await superValidate(
		{
			name: model.data.attributes.name,
			description: model.data.attributes.description,
			status: model.data.attributes.status
		},
		zod4(AssetUpdateSchema)
	);
	const credentialsForm = await superValidate(
		{
			host: asset.host,
			port: asset.port,
			dbms: asset.dbms,
			username: adminAccount?.attributes?.username,
			password: ''
		},
		zod4(AssetCredentialsSchema),
		{ errors: false }
	);
	return {
		model,
		asset,
		editForm,
		credentialsForm,
		canUpdate: rbac.canAssetUpdate(),
		canUpdateAdminAccount: rbac.canAssetUpdateAdminAccount(),
		canDelete: rbac.canAssetDelete(),
		canAddAccessGrant: rbac.canAssetAddAccessGrant(),
		canRemoveAccessGrant: rbac.canAssetRemoveAccessGrant(),
		canTestConnection: rbac.canAssetUpdateAdminAccount()
	};
};
