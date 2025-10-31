import type { LayoutLoad } from './$types';
import { layoutStore } from '$lib/stores/layout';

export const load: LayoutLoad = async ({ data }) => {
	const { me, currentOrgId } = data;
	layoutStore.setMe(me);
	layoutStore.setCurrentOrgId(currentOrgId);
};
