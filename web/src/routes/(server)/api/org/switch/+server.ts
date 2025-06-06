import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { UserService } from '$services/server/user.js';
import { getAuthToken, setCurrentOrgCookie } from '$server/helpers/cookie.js';

export const POST: RequestHandler = async ({ request, cookies }) => {
	try {
		const { orgId } = await request.json();

		// Validate orgId
		if (!orgId || typeof orgId !== 'number') {
			return json({ error: 'Invalid organization ID' }, { status: 400 });
		}

		// Get auth token to validate user access
		const token = getAuthToken(cookies);
		if (!token) {
			return json({ error: 'Unauthorized' }, { status: 401 });
		}

		// Check if user has access to the org
		const userService = new UserService(token);
		const hasAccess = await userService.checkOrgAccess(orgId);
		if (!hasAccess) {
			return json({ error: 'Unauthorized access to organization' }, { status: 403 });
		}

		// Set the currentOrgId cookie
		setCurrentOrgCookie(cookies, orgId);

		return json({
			success: true,
			currentOrgId: orgId
		});
	} catch (error) {
		console.error('Error switching org:', error);
		return json({ error: 'Failed to switch organization' }, { status: 500 });
	}
};
