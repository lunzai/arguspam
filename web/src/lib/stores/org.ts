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
		setOrgData: (orgs: OrgState['orgs'], currentOrgId: number | null) =>
			update((state) => ({
				...state,
				orgs,
				currentOrgId
			})),
		getCurrentOrg: (state: OrgState) => {
			return state.orgs.find(org => org.id === state.currentOrgId) || null;
		},
		reset: () => set(initialState)
	};
}

export const orgStore = createOrgStore(); 