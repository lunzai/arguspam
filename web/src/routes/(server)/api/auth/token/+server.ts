import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { getAuthToken } from '$lib/server/helpers/cookie.js';

export const GET: RequestHandler = async ({ cookies }) => {
	const token = getAuthToken(cookies);
	
	if (!token) {
		return json({ token: null }, { status: 200 });
	}
	
	return json({ token });
}; 