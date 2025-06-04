import type { User } from "$models/user.js";

export interface AuthState {
	user: User | null;
	isAuthenticated: boolean;
	// Computed auth states
	isEmailVerified: boolean;
	isTwoFactorEnabled: boolean;
	isTwoFactorVerified: boolean;
	// Action states
	shouldChallengeTwoFactor: boolean;  // User needs to enter OTP
	shouldSetupTwoFactor: boolean;      // User needs to setup 2FA (blocks access)
	shouldVerifyEmail: boolean;         // User should verify email (warning only)
}

export interface LoginRequest {
	email: string;
	password: string;
}

export interface LoginResponse {
	data: {
		token: string;
		user: User;
	};
} 