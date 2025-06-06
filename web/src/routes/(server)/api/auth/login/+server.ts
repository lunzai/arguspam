import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { authService } from '$services/server/auth.js';
import { setAuthToken, setCurrentOrgCookie } from '$server/helpers/cookie.js';
import { UserService } from '$services/server/user.js';
import type { LoginResponse } from '$types/auth.js';
import type { ApiError } from '$types/error.js';
import type { Org } from '$lib/types/models/org';

export const POST: RequestHandler = async ({ request, cookies }) => {
	try {
		const { email, password } = await request.json();

		if (!email || !password) {
			return json({ message: 'Email and password are required', errors: {} }, { status: 400 });
		}

		const response = (await authService.login(email, password)) as LoginResponse;
		// Set the token in an HttpOnly cookie
		setAuthToken(cookies, response.data.token);

		// Get user's organizations
		let orgs: Org[] = [];
		let currentOrgId: number | null = null;

		try {
			const userService = new UserService(response.data.token);
			const orgsResponse = await userService.getOrgs();
			orgs = orgsResponse.data.map((org) => org.attributes);
			if (orgs.length > 0) {
				currentOrgId = orgs[0].id;
				setCurrentOrgCookie(cookies, currentOrgId);
			}
		} catch (orgError) {
			console.error('Failed to fetch user orgs:', orgError);
			// Don't fail login if org fetch fails
		}

		return json({
			data: {
				user: response.data.user,
				orgs,
				currentOrgId
			}
		});
	} catch (error) {
		console.error('Login error:', error);
		const apiError = error as ApiError;
		return json(
			{
				message: apiError.message || 'Login failed',
				errors: apiError.errors || {}
			},
			{ status: apiError.status || 500 }
		);
	}
};
