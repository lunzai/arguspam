import { writable } from 'svelte/store';
import type { User, AuthState } from '$lib/types/auth.js';

const initialState: AuthState = {
	user: null,
	isAuthenticated: false,
	isLoading: false
};

function createAuthStore() {
	const { subscribe, set, update } = writable<AuthState>(initialState);

	return {
		subscribe,
		setUser: (user: User) =>
			update((state) => ({
				...state,
				user,
				isAuthenticated: true,
				isLoading: false
			})),
		clearUser: () =>
			set({
				user: null,
				isAuthenticated: false,
				isLoading: false
			}),
		setLoading: (isLoading: boolean) =>
			update((state) => ({
				...state,
				isLoading
			})),
		reset: () => set(initialState)
	};
}

export const authStore = createAuthStore(); 