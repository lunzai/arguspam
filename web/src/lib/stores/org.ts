import { writable } from 'svelte/store';

export interface OrgState {
	orgs: Array<{
		id: number;
		name: string;
		description?: string;
		status: 'active' | 'inactive';
	}>;
	currentOrgId: number | null;
}

const initialState: OrgState = {
	orgs: [],
	currentOrgId: null
};

function createOrgStore() {
	const { subscribe, set, update } = writable<OrgState>(initialState);

	return {
		subscribe,
		setOrgs: (orgs: OrgState['orgs']) =>
			update((state) => ({
				...state,
				orgs
			})),
		setCurrentOrgId: (orgId: number | null) =>
			update((state) => ({
				...state,
				currentOrgId: orgId
			})),
		getCurrentOrg: (state: OrgState) => {
			let org = state.orgs.find(org => org.id === state.currentOrgId);
			if (!org) {
				org = state.orgs[0];
			}
			return org;
		},
		reset: () => set(initialState)
	};
}

export const orgStore = createOrgStore(); 