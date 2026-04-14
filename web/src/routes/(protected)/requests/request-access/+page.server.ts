import { UserService } from '$services/user';
import type { ApiAssetCollection } from '$lib/resources/asset.js';
import { Rbac } from '$lib/rbac';

export const load = async ({ params, locals, depends }) => {
	depends('requests:asset');
	new Rbac(locals.me).assetViewRequestable();
	const { authToken, currentOrgId } = locals;
	const userService = new UserService(authToken as string, currentOrgId as number);
	const assetCollection = (await userService.getRequesterAssets()) as ApiAssetCollection;
	return {
		assetCollection,
		title: `Request Access`,
	};
};
