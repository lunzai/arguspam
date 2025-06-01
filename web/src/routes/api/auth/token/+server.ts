import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import config from '$lib/config';

export const GET: RequestHandler = async ({ cookies }) => {
    try {
        const token = cookies.get(config.auth.tokenKey);
        
        if (!token) {
            return json({ token: null }, { status: 401 });
        }
        
        return json({ token });
    } catch (error) {
        console.error('Error getting auth token:', error);
        return json({ token: null }, { status: 500 });
    }
}; 