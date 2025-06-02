import type { LayoutLoad } from './$types';
import { authStore } from '$lib/stores/auth.js';

export const load = (async ({ data }) => {
	// Initialize auth store with server-provided user data
	if (data.user) {
		authStore.setUser(data.user);
	}

	return {
		user: data.user,
		url: data.url
	};
}) satisfies LayoutLoad;
