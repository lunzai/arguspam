import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { UserService } from '$services/user';
import { getAuthToken, setCurrentOrgId } from '$utils/cookie';

export const POST: RequestHandler = async ({ request, cookies, params }) => {
	try {
		const { orgId } = await request.json();
		if (!orgId || typeof orgId !== 'number') {
			return json({ error: 'Invalid organization ID' }, { status: 400 });
		}
		const token = getAuthToken(cookies);
		if (!token) {
			return json({ error: 'Unauthorized' }, { status: 401 });
		}
		const userService = new UserService(token);
		const orgAccess = await userService.checkOrgAccess(orgId);
		if (!orgAccess) {
			return json({ error: 'Unauthorized access to organization' }, { status: 403 });
		}
		setCurrentOrgId(cookies, orgId);

		return json({
			success: true,
			currentOrgId: orgId
		});
	} catch (error) {
		return json({ error: 'Failed to switch organization' }, { status: 500 });
	}
};
