import type { LayoutLoad } from './$types';
import { layoutStore } from '$lib/stores/layout';
import { Rbac } from '$lib/rbac';

export const load: LayoutLoad = async ({ data }) => {
	const { me, currentOrgId } = data;
	layoutStore.setMe(me);
	layoutStore.setCurrentOrgId(currentOrgId);
	const rbac = new Rbac(me);
	return {
		rbac
	};
};
