import type { User } from '$models/user.js';

export interface AuthState {
	user: User;
	isAuthenticated: boolean;
	// Computed auth states
	isEmailVerified: boolean;
	isTwoFactorEnabled: boolean;
	isTwoFactorVerified: boolean;
	// Action states
	shouldChallengeTwoFactor: boolean; // User needs to enter OTP
	shouldSetupTwoFactor: boolean; // User needs to setup 2FA (blocks access)
	shouldVerifyEmail: boolean; // User should verify email (warning only)
}

export interface LoginResponse {
	data: {
		token: string | null;
		user: User;
		temp_key: string | null;
		temp_key_expires_at: string | null;
		requires_2fa: boolean | null;
	};
}

export interface Login2faResponse {
	data: {
		token: string;
		user: User;
	};
}