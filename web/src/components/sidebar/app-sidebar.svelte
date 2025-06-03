<script lang="ts" module>
	import {
		LayoutDashboardIcon,
		ServerIcon,
		SquareTerminalIcon,
		Building2,
		Users,
		Settings2Icon
	} from '@lucide/svelte';
	import { avatarService } from '$lib/client/services/avatar.js';

	// This is sample data.
	const data = {
		user: {
			name: 'HL LEONG',
			email: 'heanluen@surfin.sg',
			avatar: avatarService.avatar('heanluen@surfin.sg')
		},
		orgs: [
			{
				name: 'Acme Inc',
				logo: avatarService.initial('Acme Inc'),
				plan: 'Enterprise'
			},
			{
				name: 'Acme Corp.',
				logo: avatarService.initial('Acme Corp.'),
				plan: 'Startup'
			},
			{
				name: 'Evil Corp.',
				logo: avatarService.initial('Evil Corp.'),
				plan: 'Free'
			}
		],
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

<script lang="ts">
	import NavMain from './nav-main.svelte';
	import NavUser from './nav-user.svelte';
	import OrgSwitcher from './org-switcher.svelte';
	import * as Sidebar from '$ui/sidebar/index.js';
	import type { ComponentProps } from 'svelte';

	let {
		ref = $bindable(null),
		collapsible = 'icon',
		...restProps
	}: ComponentProps<typeof Sidebar.Root> = $props();
</script>

<Sidebar.Root {collapsible} {...restProps}>
	<Sidebar.Header>
		<OrgSwitcher orgs={data.orgs} />
	</Sidebar.Header>
	<Sidebar.Content>
		<NavMain items={data.navMain} />
	</Sidebar.Content>
	<Sidebar.Footer>
		<NavUser user={data.user} />
	</Sidebar.Footer>
	<Sidebar.Rail />
</Sidebar.Root>
