import { json, error } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { serverApi } from '$lib/server/api';

export const GET: RequestHandler = async (event) => {
	try {
		const response = await serverApi.get(event, '/users/me');
		return json(response.data);
	} catch (err: any) {
		console.error('Error fetching user:', err.response?.data || err.message);
		
		if (err.response?.status === 401) {
			return error(401, 'Unauthorized');
		}
		
		return error(500, 'Internal server error');
	}
}; 