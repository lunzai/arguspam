<script lang="ts">
	import * as Collapsible from '$ui/collapsible';
	import * as Sidebar from '$ui/sidebar';
	import { page } from '$app/state';
	import {
		ChevronRight,
		LayoutDashboardIcon,
		ServerIcon,
		SquareTerminalIcon,
		Building2,
		Users,
		Settings2Icon,
		MessageSquare,
		Cctv,
		ClipboardPlus
	} from '@lucide/svelte';
	import type { Component } from 'svelte';

	const { rbac } = page.data;
	const pathname = $state(page.url.pathname);

	interface NavItem {
		title: string;
		url: string;
		icon: Component;
		isActive?: boolean;
		visible: boolean;
		items: Array<{
			title: string;
			url: string;
			visible: boolean;
		}>;
	}

	const canViewSettings = $derived(
		rbac.canUserView() &&
			(rbac.canUserUpdate() ||
				rbac.canUserChangePassword() ||
				rbac.canUserEnrollTwoFactorAuthentication())
	);

	const platformNavItems = [
		{
			title: 'Dashboard',
			url: '/dashboard',
			icon: LayoutDashboardIcon,
			isActive: pathname.startsWith('/dashboard'),
			visible: true
		},
		// {
		// 	title: 'Announcements',
		// 	url: '/announcements',
		// 	icon: MessageSquare,
		// 	isActive: pathname.startsWith('/announcements')
		// },
		{
			title: 'Requests',
			url: '#',
			icon: ClipboardPlus,
			isActive: pathname.startsWith('/requests'),
			visible: rbac.canRequestView(),
			items: [
				{
					title: 'Create Request',
					url: '/requests/assets',
					visible: rbac.canRequestCreate()
				},
				// {
				// 	title: 'Pending Approval',
				// 	url: '/requests/pending-approval',
				//     visible: true,
				// },
				{
					title: 'All Requests',
					url: '/requests',
					visible: true
				}
			]
		},
		{
			title: 'Sessions',
			url: '/sessions',
			icon: SquareTerminalIcon,
			isActive: pathname.startsWith('/sessions'),
			visible: rbac.canSessionView()
			// items: [
			// 	{
			// 		title: 'My Sessions',
			// 		url: '/sessions/my-sessions',
			//         visible: rbac.canSessionView(),
			// 	},
			// 	{
			// 		title: 'All Sessions',
			// 		url: '/sessions',
			//         visible: rbac.canSessionView(),
			// 	}
			// ]
		},
		{
			title: 'Settings',
			url: '/settings/account',
			icon: Settings2Icon,
			// svelte-ignore state_referenced_locally
			visible: canViewSettings
		}
	] as NavItem[];

	const adminNavItems = [
		{
			title: 'Assets',
			url: '/assets',
			icon: ServerIcon,
			isActive: pathname.startsWith('/assets'),
			visible: rbac.canAssetView()
		},
		{
			title: 'Organizations',
			url: '#',
			icon: Building2,
			isActive: pathname.startsWith('/organizations'),
			visible: rbac.canOrgView() || rbac.canUserGroupView(),
			items: [
				{
					title: 'Organizations',
					url: '/organizations',
					visible: rbac.canOrgView()
				},
				{
					title: 'User Groups',
					url: '/organizations/user-groups',
					visible: rbac.canUserGroupView()
				}
			]
		},
		{
			title: 'Users',
			url: '#',
			icon: Users,
			isActive: pathname.startsWith('/users'),
			visible: rbac.canUserViewAny() || rbac.canRoleView() || rbac.canPermissionView(),
			items: [
				{
					title: 'Users',
					url: '/users',
					visible: rbac.canUserViewAny()
				},
				{
					title: 'Roles',
					url: '/users/roles',
					visible: rbac.canRoleView()
				},
				{
					title: 'Permissions',
					url: '/users/permissions',
					visible: rbac.canPermissionView()
				}
			]
		}
	] as NavItem[];

	const platformNavIsVisible = $derived(platformNavItems.some((item) => item.visible));
	const adminNavIsVisible = $derived(adminNavItems.some((item) => item.visible));
</script>

{#if platformNavIsVisible}
	{@render navGroup('Workbench', platformNavItems)}
{/if}
{#if adminNavIsVisible}
	{@render navGroup('Administration', adminNavItems)}
{/if}

{#snippet navGroup(title: string, items: NavItem[])}
	<Sidebar.Group>
		<Sidebar.GroupLabel>{title}</Sidebar.GroupLabel>
		<Sidebar.Menu>
			{#each items as item (item.title)}
				{#if item.visible}
					{#if item.items}
						<Collapsible.Root open={item.isActive} class="group/collapsible">
							{#snippet child({ props })}
								<Sidebar.MenuItem {...props}>
									<Collapsible.Trigger>
										{#snippet child({ props })}
											<Sidebar.MenuButton {...props} tooltipContent={item.title}>
												{#if item.icon}
													<item.icon />
												{/if}
												<span>{item.title}</span>
												<ChevronRight
													class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
												/>
											</Sidebar.MenuButton>
										{/snippet}
									</Collapsible.Trigger>
									<Collapsible.Content>
										<Sidebar.MenuSub>
											{#each item.items ?? [] as subItem (subItem.title)}
												{#if subItem.visible}
													<Sidebar.MenuSubItem>
														<Sidebar.MenuSubButton>
															{#snippet child({ props })}
																<a href={subItem.url} {...props}>
																	<span>{subItem.title}</span>
																</a>
															{/snippet}
														</Sidebar.MenuSubButton>
													</Sidebar.MenuSubItem>
												{/if}
											{/each}
										</Sidebar.MenuSub>
									</Collapsible.Content>
								</Sidebar.MenuItem>
							{/snippet}
						</Collapsible.Root>
					{:else}
						<Sidebar.MenuItem>
							<Sidebar.MenuButton tooltipContent={item.title}>
								{#snippet child({ props })}
									<a href={item.url} {...props}>
										{#if item.icon}
											<item.icon />
										{/if}
										<span>{item.title}</span>
									</a>
								{/snippet}
							</Sidebar.MenuButton>
						</Sidebar.MenuItem>
					{/if}
				{/if}
			{/each}
		</Sidebar.Menu>
	</Sidebar.Group>
{/snippet}
