import type { LayoutLoad } from './$types';
import { authStore } from '$stores/auth.js';
import { orgStore } from '$lib/stores/org';

export const load: LayoutLoad = async ({ data }) => {
	if (data.user) {
		authStore.setUser(data.user);
	}
	if (data.orgs) {
		orgStore.setOrgs(data.orgs);
	}
	if (data.currentOrgId) {
		orgStore.setCurrentOrgId(data.currentOrgId);
	}
	return {
		data
	};
};
