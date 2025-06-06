import type { LayoutLoad } from './$types';
import { authStore } from '$stores/auth.js';
import { orgStore } from '$lib/stores/org';

export const load: LayoutLoad = async ({ data }) => {
	// Set user data in client store when layout loads
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
		user: data.user,
		orgs: data.orgs,
		currentOrgId: data.currentOrgId
	};
};
