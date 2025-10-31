import { writable } from 'svelte/store';
import type { Org } from '$models/org';
import type { GroupedPermission } from '$lib/resources/permission';
import type { UserGroup } from '$lib/models/user-group';
import type { Role } from '$models/role';
import type { Me, User } from '$models/user';

export interface LayoutState {
	user: User;
	sidebarOpen: boolean;
	currentOrgId: number | null;
	currentOrg: Org | null;
	orgs: Org[];
	permissions: GroupedPermission;
	roles: Role[];
	groups: UserGroup[];
	scheduledSessionsCount: number;
	submittedRequestsCount: number;
}

const initialState: LayoutState = {
	user: {} as User,
	sidebarOpen: false,
	currentOrgId: null,
	currentOrg: null,
	orgs: [],
	permissions: [],
	roles: [],
	groups: [],
	scheduledSessionsCount: 0,
	submittedRequestsCount: 0
};

function createLayoutStore() {
	const { subscribe, set, update } = writable<LayoutState>(initialState);
	return {
		subscribe,
		setMe: (me: Me) =>
			update((state) => ({
				...state,
				user: me,
				orgs: me.orgs,
				permissions: me.permissions,
				roles: me.roles,
				groups: me.user_groups,
				scheduledSessionsCount: me.scheduled_sessions_count,
				submittedRequestsCount: me.submitted_requests_count
			})),
		setUser: (user: User) => update((state) => ({ ...state, user })),
		setSidebarOpen: (sidebarOpen: boolean) => update((state) => ({ ...state, sidebarOpen })),
		setCurrentOrgId: (currentOrgId: number | null) =>
			update((state) => ({
				...state,
				currentOrgId,
				currentOrg: state.orgs.find((org) => org.id === currentOrgId) || null
			})),
		setOrgs: (orgs: Org[]) => update((state) => ({ ...state, orgs })),
		setPermissions: (permissions: GroupedPermission) =>
			update((state) => ({ ...state, permissions })),
		setRoles: (roles: Role[]) => update((state) => ({ ...state, roles })),
		setGroups: (groups: UserGroup[]) => update((state) => ({ ...state, groups })),
		setScheduledSessionsCount: (scheduledSessionsCount: number) =>
			update((state) => ({ ...state, scheduledSessionsCount })),
		setSubmittedRequestsCount: (submittedRequestsCount: number) =>
			update((state) => ({ ...state, submittedRequestsCount })),
		reset: () => set(initialState)
	};
}

export const layoutStore = createLayoutStore();
