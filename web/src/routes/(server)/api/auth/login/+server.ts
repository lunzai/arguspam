import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { AuthService } from '$services/auth';
import { setAuthToken, setCurrentOrgId } from '$utils/cookie';
import { UserService } from '$services/user';
import type { LoginResponse } from '$resources/auth';
// import type { ApiError } from '$resources/error';
import type { Org } from '$models/org';

export const POST: RequestHandler = async ({ request, cookies }) => {
	try {
		const { email, password } = await request.json();

		if (!email || !password) {
			return json({ message: 'Email and password are required', errors: {} }, { status: 400 });
		}

		// const response = (await authService.login(email, password)) as LoginResponse;
		// // Set the token in an HttpOnly cookie
		// setAuthToken(cookies, response.data.token);

		// // Get user's organizations
		// let orgs: Org[] = [];
		// let currentOrgId: number | null = null;

		// try {
		// 	const userService = new UserService(response.data.token);
		// 	const orgsResponse = await userService.getOrgs();
		// 	orgs = orgsResponse.data.map((org) => org.attributes);
		// 	if (orgs.length > 0) {
		// 		currentOrgId = orgs[0].id;
		// 		setCurrentOrgCookie(cookies, currentOrgId);
		// 	}
		// } catch (orgError) {
		// 	console.error('Failed to fetch user orgs:', orgError);
		// 	// Don't fail login if org fetch fails
		// }

		return json({
			data: {
				// user: response.data.user,
				// orgs,
				// currentOrgId
			}
		});
	} catch (error) {
		console.error('Login error:', error);
		// const apiError = error as ApiError;
		// return json(
		// 	{
		// 		message: apiError.message || 'Login failed',
		// 		errors: apiError.errors || {}
		// 	},
		// 	{ status: apiError.status || 500 }
		// );
	}
};
