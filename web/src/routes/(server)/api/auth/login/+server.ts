import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { authService } from '$services/server/auth.js';
import { setAuthCookie } from '$server/helpers/cookie.js';
import { userService } from '$services/server/user.js';
import type { LoginResponse } from '$types/auth.js';
import type { ApiError } from '$types/error.js';
import { CURRENT_ORG_KEY } from '$env/static/private';

export const POST: RequestHandler = async ({ request, cookies }) => {
	try {
		const { email, password } = await request.json();

		if (!email || !password) {
			return json({ message: 'Email and password are required', errors: {} }, { status: 400 });
		}

		const response = (await authService.login(email, password)) as LoginResponse;
		// Set the token in an HttpOnly cookie
		setAuthCookie(cookies, response.data.token);

		// Get user's organizations and set default org if not already set
		try {
			const currentOrgId = cookies.get(CURRENT_ORG_KEY);
			if (!currentOrgId) {
				const orgsResponse = await userService.getOrgs(response.data.token);
				if (orgsResponse.data && orgsResponse.data.length > 0) {
					// Set first org as default
					const firstOrg = orgsResponse.data[0].attributes;
					if (firstOrg && firstOrg.id) {
						console.log('firstOrg.id', firstOrg.id);
						cookies.set(CURRENT_ORG_KEY, firstOrg.id.toString(), {
							path: '/',
							httpOnly: true,
							secure: true,
							sameSite: 'strict',
							maxAge: 60 * 60 * 24 * 30 // 30 days
						});
					}
				}
			}
		} catch (orgError) {
			console.error('Failed to set default org:', orgError);
			// Don't fail login if org setup fails
		}

		// Return user data without the token (flatten the structure)
		return json({
			data: {
				user: response.data.user
			}
		});
	} catch (error) {
		//console.error('Login error:', error);
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
