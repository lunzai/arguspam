import { clientApi } from '$lib/api/client.js';
import { orgStore } from '$stores/org.js';

export class OrgSwitchService {
	/**
	 * Switch to a different organization
	 * @param orgId - The organization ID to switch to
	 */
	async switchOrg(orgId: number): Promise<void> {
		try {
			// Call the API to update the server-side cookie
			await clientApi.internal().post('/api/org/switch', { orgId });
			
			// Update the client-side store
			orgStore.setCurrentOrgId(orgId);
		} catch (error) {
			console.error('Failed to switch organization:', error);
			throw error;
		}
	}
}

export const orgSwitchService = new OrgSwitchService(); 