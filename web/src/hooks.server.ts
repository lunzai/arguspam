import { redirect, type Handle } from '@sveltejs/kit';
import { clearAuthCookie, clearCurrentOrgId, getAuthToken, getCurrentOrgId, setCurrentOrgId } from '$utils/cookie';
import { PUBLIC_AUTH_LOGIN_PATH } from '$env/static/public';
import { AuthService } from '$lib/services/auth';
import { UserService } from '$services/user';
import type { UserResource } from '$resources/user';

export const handle: Handle = async ({ event, resolve }) => {
    
    const authToken = getAuthToken(event.cookies);
    let currentOrgId = getCurrentOrgId(event.cookies);
    event.locals.authToken = authToken;
    event.locals.currentOrgId = currentOrgId || undefined;

    if (event.route.id?.startsWith('/(protected)') || event.route.id?.startsWith('/(server)/api')) {    
        if (!authToken) {
            return redirect(302, PUBLIC_AUTH_LOGIN_PATH);
        }
        try {
            const authService = new AuthService(authToken);
            const userResource: UserResource = await authService.me();
            const userService = new UserService(authToken);
            const orgCollection = await userService.getOrgs();
            if (!currentOrgId || !(await userService.checkOrgAccess(currentOrgId))) {
                currentOrgId = orgCollection.data[0].attributes.id;
                setCurrentOrgId(event.cookies, currentOrgId);
            }
            event.locals.user = userResource.data.attributes;
            event.locals.userOrgs = orgCollection.data;
        } catch (error) {
            clearAuthCookie(event.cookies);
            clearCurrentOrgId(event.cookies);
            return redirect(302, PUBLIC_AUTH_LOGIN_PATH);
        }
    }
    return await resolve(event);
};
