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