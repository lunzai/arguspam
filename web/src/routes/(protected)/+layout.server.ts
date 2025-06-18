import type { LayoutServerLoad } from './$types';
import { redirect } from '@sveltejs/kit';
import {
	clearAuthCookie,
	clearCurrentOrgId,
	getAuthToken,
	getCurrentOrgId,
	setCurrentOrgId
} from '$utils/cookie';
import { AuthService } from '$services/auth';
import { UserService } from '$services/user';
import type { UserResource } from '$resources/user';
import { PUBLIC_AUTH_LOGIN_PATH } from '$env/static/public';

export const load: LayoutServerLoad = async ({ cookies }) => {
	const token = getAuthToken(cookies);
	let currentOrgId = getCurrentOrgId(cookies);
	if (!token) {
		clearAuthCookie(cookies);
		clearCurrentOrgId(cookies);
		throw redirect(302, PUBLIC_AUTH_LOGIN_PATH);
	}
	try {
		const authService = new AuthService(token);
		const userResource: UserResource = await authService.me();
		// TODO: getOrgs API is paginated, we need to handle that
		const userService = new UserService(token);
		const orgCollection = await userService.getOrgs();
		if (!currentOrgId || !(await userService.checkOrgAccess(currentOrgId))) {
			currentOrgId = orgCollection.data[0].attributes.id;
			setCurrentOrgId(cookies, currentOrgId);
		}
		return {
			user: userResource.data.attributes,
			orgs: orgCollection.data,
			currentOrgId
		};
	} catch (error) {
		// If token is invalid, redirect to login
		console.error('Error loading layout', error);
		throw redirect(302, PUBLIC_AUTH_LOGIN_PATH);
	}
};
