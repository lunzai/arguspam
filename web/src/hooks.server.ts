import { redirect, type Handle } from '@sveltejs/kit';
import {
	clearAuthCookie,
	clearCurrentOrgId,
	getAuthToken,
	getCurrentOrgId,
	setAuthToken,
	setCurrentOrgId
} from '$utils/cookie';
import { PUBLIC_AUTH_LOGIN_PATH } from '$env/static/public';
import { AuthService } from '$lib/services/auth';
import type { ApiMeResource } from '$resources/user';
import type { Me } from '$models/user';

export const handle: Handle = async ({ event, resolve }) => {
	if (event.route.id?.startsWith('/(protected)') || event.route.id?.startsWith('/(server)/api')) {
		const authToken = getAuthToken(event.cookies);
		let currentOrgId = getCurrentOrgId(event.cookies);

		if (!authToken) {
			return redirect(302, PUBLIC_AUTH_LOGIN_PATH);
		}
		try {
			const authService = new AuthService(authToken);
			const meResource: ApiMeResource = await authService.me();
			const me = meResource.data.attributes as Me;
			if (!currentOrgId) {
				currentOrgId = me.orgs[0]?.id || null;
				if (currentOrgId) {
					setCurrentOrgId(event.cookies, currentOrgId);
				}
			}
			event.locals.me = me;
			event.locals.currentOrgId = currentOrgId;
			event.locals.authToken = authToken;
			setAuthToken(event.cookies, authToken);
		} catch (error) {
			clearAuthCookie(event.cookies);
			clearCurrentOrgId(event.cookies);
			return redirect(302, PUBLIC_AUTH_LOGIN_PATH);
		}
	}
	return await resolve(event);
};
