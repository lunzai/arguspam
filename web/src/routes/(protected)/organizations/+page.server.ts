import type { PageServerLoad } from './$types';
import { OrgService } from '$services/org';

export const load: PageServerLoad = async ({ locals }) => {
	const { authToken, currentOrgId } = locals;

	try {
		const orgService = new OrgService(authToken as string, currentOrgId);
		const orgCollection = await orgService.findAll();
		return {
			orgCollection,
			title: 'Organizations'
		};
	} catch (error) {
		console.error('Error fetching organizations', error);
	}
};
