import { writable, derived } from 'svelte/store';
import { browser } from '$app/environment';
import type { User, AuthState } from '$lib/types';

// Get user data from cookie if available
function getUserFromCookie(): User | null {
	if (!browser) return null;
	
	try {
		const userCookie = document.cookie
			.split('; ')
			.find(row => row.startsWith('user_data='));
		
		if (userCookie) {
			const userData = decodeURIComponent(userCookie.split('=')[1]);
			return JSON.parse(userData);
		}
	} catch (error) {
		console.error('Error parsing user cookie:', error);
	}
	
	return null;
}

// Helper function to compute auth state properties
function computeAuthState(user: User | null): AuthState {
	return {
		user,
		isAuthenticated: user !== null, // No client-side token check needed
		isEmailVerified: user?.email_verified_at !== null,
		isTwoFactorEnabled: user?.two_factor_enabled || false,
		isTwoFactorConfirmed: user?.two_factor_confirmed_at !== null
	};
}

// Create the auth store
function createAuthStore() {
	const initialUser = getUserFromCookie();
	const initialState = computeAuthState(initialUser);

	const { subscribe, set, update } = writable<AuthState>(initialState);

	return {
		subscribe,
		// Initialize auth state (call this in your app's layout)
		init: () => {
			if (browser) {
				const user = getUserFromCookie();
				set(computeAuthState(user));
			}
		},
		// Update user data
		setUser: (user: User | null) => {
			set(computeAuthState(user));
		},
		// Clear auth state
		clear: () => {
			set(computeAuthState(null));
		}
	};
}

export const auth = createAuthStore();

// Derived stores for convenience
export const user = derived(auth, $auth => $auth.user);
export const isAuthenticated = derived(auth, $auth => $auth.isAuthenticated);
export const isEmailVerified = derived(auth, $auth => $auth.isEmailVerified);
export const isTwoFactorEnabled = derived(auth, $auth => $auth.isTwoFactorEnabled);
export const isTwoFactorConfirmed = derived(auth, $auth => $auth.isTwoFactorConfirmed); 