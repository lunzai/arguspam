import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { userService } from '$services/client/users.js';
import { getAuthToken } from '$server/helpers/cookie.js';
import { CURRENT_ORG_KEY } from '$env/static/private';

export const POST: RequestHandler = async ({ request, cookies }) => {
	try {
		const { orgId } = await request.json();

		// Validate orgId
		if (!orgId || typeof orgId !== 'number') {
			return json(
				{ error: 'Invalid organization ID' },
				{ status: 400 }
			);
		}

		// Get auth token to validate user access
		const token = getAuthToken(cookies);
		if (!token) {
			return json(
				{ error: 'Unauthorized' },
				{ status: 401 }
			);
		}

		// Check if user has access to the org
		const hasAccess = await userService.checkOrgAccess(orgId);
		if (!hasAccess) {
			return json(
				{ error: 'Unauthorized access to organization' },
				{ status: 403 }
			);
		}

		// Set the currentOrgId cookie
		cookies.set(CURRENT_ORG_KEY, orgId.toString(), {
			path: '/',
			httpOnly: true,
			secure: true,
			sameSite: 'strict',
			maxAge: 60 * 60 * 24 * 30 // 30 days
		});

		return json({ 
			success: true,
			currentOrgId: orgId
		});

	} catch (error) {
		console.error('Error switching org:', error);
		return json(
			{ error: 'Failed to switch organization' },
			{ status: 500 }
		);
	}
}; 