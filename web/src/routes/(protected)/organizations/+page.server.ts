import type { PageServerLoad } from './$types';
import { OrgService } from '$services/org';
import { getAuthToken, getCurrentOrgId } from '$utils/cookie';

export const load: PageServerLoad = async ({ parent, cookies }) => {
    const token = getAuthToken(cookies);
    const orgId = getCurrentOrgId(cookies);

    if (!token || !orgId) {
		console.log('No token or orgId found');
        // redirect(302, '/login');
    }
	try {
		const orgService = new OrgService(token as string, Number(orgId));
		const orgCollection = await orgService.findAll();	
		return {
			// user
			orgCollection,
			title: 'Organizations'
		};
	} catch (error) {
		console.error('Error fetching organizations', error);
	}
};