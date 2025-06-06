import type { LayoutServerLoad } from './$types';
import { redirect } from '@sveltejs/kit';
import { getAuthToken, getCurrentOrg, setCurrentOrgCookie } from '$server/helpers/cookie.js';
import { authService } from '$services/server/auth.js';
import { UserService } from '$services/server/user.js';
import type { User } from '$models/user.js';
import { PUBLIC_AUTH_LOGIN_PATH } from '$env/static/public';
import type { Org } from '$lib/types/models/org';

export const load: LayoutServerLoad = async ({ cookies }) => {
	const token = getAuthToken(cookies);
	let currentOrgId = getCurrentOrg(cookies);
	if (!token) {
		throw redirect(302, PUBLIC_AUTH_LOGIN_PATH);
	}
	try {
		const user: User = await authService.me(token);
		// TODO: getOrgs API is paginated, we need to handle that
		const userService = new UserService(token);
		const orgs: Org[] = (await userService.getOrgs()).data
			.map((org) => org.attributes);
		if (currentOrgId && !(await userService.checkOrgAccess(currentOrgId))) {
			currentOrgId = orgs[0].id;
			setCurrentOrgCookie(cookies, currentOrgId);
		}
		return {
			user,
			orgs,
			currentOrgId
		};
	} catch (error) {
		// If token is invalid, redirect to login
		throw redirect(302, PUBLIC_AUTH_LOGIN_PATH);
	}
};
