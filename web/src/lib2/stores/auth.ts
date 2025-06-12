import { writable } from 'svelte/store';
import type { AuthState } from '$types/auth.js';
import type { User } from '$models/user.js';
import type { Org } from '$models/org';

const initialState: AuthState = {
	user: null,
	isAuthenticated: false,
	isEmailVerified: false,
	isTwoFactorEnabled: false,
	isTwoFactorVerified: false,
	shouldChallengeTwoFactor: false,
	shouldSetupTwoFactor: false,
	shouldVerifyEmail: false
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
				isEmailVerified: user.email_verified_at !== null,
				isTwoFactorEnabled: user.two_factor_enabled,
				isTwoFactorVerified: user.two_factor_confirmed_at !== null,
				shouldChallengeTwoFactor: user.two_factor_enabled && user.two_factor_confirmed_at !== null,
				shouldSetupTwoFactor: user.two_factor_enabled && user.two_factor_confirmed_at === null,
				shouldVerifyEmail: user.email_verified_at === null
			})),
		clearUser: () => set(initialState),
		reset: () => set(initialState)
	};
}

export const authStore = createAuthStore();
