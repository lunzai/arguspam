import { json, error } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { serverApi } from '$lib/server/api';

export const GET: RequestHandler = async (event) => {
	try {
		const response = await serverApi.get(event, '/users', {
			params: Object.fromEntries(event.url.searchParams)
		});
		return json(response.data);
	} catch (err: any) {
		console.error('Error fetching users:', err.response?.data || err.message);
		
		if (err.response?.status === 401) {
			return error(401, 'Unauthorized');
		}
		
		return error(500, 'Internal server error');
	}
};

export const POST: RequestHandler = async (event) => {
	try {
		const body = await event.request.json();
		const response = await serverApi.post(event, '/users', body);
		return json(response.data, { status: 201 });
	} catch (err: any) {
		console.error('Error creating user:', err.response?.data || err.message);
		
		if (err.response?.status === 401) {
			return error(401, 'Unauthorized');
		}
		
		if (err.response?.status === 422) {
			return error(422, err.response.data);
		}
		
		return error(500, 'Internal server error');
	}
}; 