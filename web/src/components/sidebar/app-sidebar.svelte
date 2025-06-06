<script lang="ts" module>
	import {
		LayoutDashboardIcon,
		ServerIcon,
		SquareTerminalIcon,
		Building2,
		Users,
		Settings2Icon
	} from '@lucide/svelte';
	import { generateAvatar, generateInitials } from '$lib/services/client/avatar.js';
</script>

<script lang="ts">
	import * as Sidebar from '$ui/sidebar/index.js';
	import NavMain from '$components/sidebar/nav-main.svelte';
	import NavUser from '$components/sidebar/nav-user.svelte';
	import OrgSwitcher from '$components/sidebar/org-switcher.svelte';
	import type { ComponentProps } from 'svelte';
	import { authStore } from '$stores/auth.js';

	let {
		ref = $bindable(null),
		collapsible = 'icon',
		...restProps
	}: ComponentProps<typeof Sidebar.Root> = $props();

	// This is sample data.
	const data = {
		navMain: [
			{
				title: 'Dashboard',
				url: '/dashboard',
				icon: LayoutDashboardIcon,
				isActive: true
			},
			{
				title: 'Assets',
				url: '/assets',
				icon: ServerIcon,
				isActive: false,
				items: [
					{
						title: 'Assets',
						url: '/assets'
					},
					{
						title: 'Accounts',
						url: '/assets'
					},
					{
						title: 'Permission',
						url: '/assets'
					}
				]
			},
			{
				title: 'Sessions',
				url: '#',
				icon: SquareTerminalIcon,
				isActive: false,
				items: [
					{
						title: 'Requests',
						url: '/requests'
					},
					{
						title: 'Sessions',
						url: '/sessions'
					},
					{
						title: 'Audits',
						url: '/session-audits'
					}
				]
			},
			{
				title: 'Organizations',
				url: '#',
				icon: Building2,
				items: [
					{
						title: 'Organizations',
						url: '/organizations'
					},
					{
						title: 'User Groups',
						url: '/user-groups'
					}
				]
			},
			{
				title: 'Users',
				url: '#',
				icon: Users,
				items: [
					{
						title: 'Users',
						url: '/users'
					},
					{
						title: 'Roles',
						url: '/roles'
					},
					{
						title: 'Permissions',
						url: '/permissions'
					}
				]
			},
			{
				title: 'Settings',
				url: '/settings',
				icon: Settings2Icon
			}
		]
	};
</script>

<Sidebar.Root {collapsible} {...restProps}>
	<Sidebar.Header>
		<OrgSwitcher />
	</Sidebar.Header>
	<Sidebar.Content>
		<NavMain items={data.navMain} />
	</Sidebar.Content>
	<Sidebar.Footer>
		<NavUser />
	</Sidebar.Footer>
	<Sidebar.Rail />
</Sidebar.Root>
