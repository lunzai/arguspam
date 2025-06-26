import type { LayoutLoad } from './$types';
import { authStore } from '$stores/auth.js';
import { orgStore } from '$lib/stores/org';

export const load: LayoutLoad = async ({ data }) => {
	if (data.user) {
		authStore.setUser(data.user);
	}
	if (data.userOrgs) {
		orgStore.setOrgs(data.userOrgs);
	}
	if (data.currentOrgId) {
		orgStore.setCurrentOrgId(data.currentOrgId);
	}
	delete data.authToken;
	return {
		data
	};
};
