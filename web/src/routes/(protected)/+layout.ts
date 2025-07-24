import type { LayoutLoad } from './$types';
import { authStore } from '$stores/auth.js';
import { orgStore } from '$lib/stores/org';

export const load: LayoutLoad = async ({ data }) => {
	const { user, currentOrgId, userOrgs } = data;
	authStore.setUser(user);
	orgStore.setOrgs(userOrgs);
	orgStore.setCurrentOrgId(currentOrgId);
	delete data.authToken;
};
