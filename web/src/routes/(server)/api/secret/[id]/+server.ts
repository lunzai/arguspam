import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { SessionService } from '$lib/services/session';

export const POST: RequestHandler = async ({ locals, params }) => {
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	try {
		const sessionService = new SessionService(authToken as string, currentOrgId);
		const response = await sessionService.retrieveSecret(Number(id));

		return json({
			success: true,
			message: 'Session secret retrieved successfully',
			data: response.data
		});
	} catch (error) {
		return json({ error: 'Failed to retrieve session secret' }, { status: 500 });
	}
};
