export interface User {
	id: number;
	name: string;
	email: string;
	email_verified_at: string | null;
	two_factor_enabled: boolean;
	two_factor_confirmed_at: string | null;
	status: string;
}

export interface AuthState {
	user: User | null;
	isAuthenticated: boolean;
	isEmailVerified: boolean;
	isTwoFactorEnabled: boolean;
	isTwoFactorConfirmed: boolean;
} 