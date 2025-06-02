export interface User {
	id: number;
	name: string;
	email: string;
	email_verified_at: string | null;
	two_factor_enabled: boolean;
	two_factor_confirmed_at: string | null;
	status: string;
	last_login_at: string | null;
	created_by: number | null;
	created_at: string;
	updated_by: number | null;
	updated_at: string;
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

export interface ValidationError {
	message: string;
	errors: Record<string, string[]>;
}

export interface AuthState {
	user: User | null;
	isAuthenticated: boolean;
	isLoading: boolean;
}

export interface ApiError {
	message: string;
	status: number;
	errors?: Record<string, string[]>;
}
