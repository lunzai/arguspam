import { json } from '@sveltejs/kit';
import type { RequestHandler } from './$types';
import { SearchService } from '$lib/services/search';

export const GET: RequestHandler = async ({ params, request, url, locals }) => {
	const searchService = new SearchService(
		params.endpoint,
		locals.authToken as string,
		locals.currentOrgId as number
	);
	const response = await searchService.findAll({
		page: Number(url.searchParams.get('page')) || 1,
		include: url.searchParams.get('include')?.split(',') || [],
		sort: url.searchParams.get('sort')?.split(',') || [],
		filter: url.searchParams.get('filter')?.split(',') || []
	});
	return json(response);
};
