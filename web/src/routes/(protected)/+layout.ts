import type { LayoutLoad } from './$types';
import { authStore } from '$lib/stores/auth.js';

export const load: LayoutLoad = async ({ data }) => {
	// Set user data in client store when layout loads
	if (data.user) {
		authStore.setUser(data.user);
	}
	
	return {
		user: data.user
	};
};
