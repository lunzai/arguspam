<script lang="ts">
	import * as Collapsible from '$ui/collapsible';
	import * as Sidebar from '$ui/sidebar';
	import {
		ChevronRight,
		LayoutDashboardIcon,
		ServerIcon,
		SquareTerminalIcon,
		Building2,
		Users,
		Settings2Icon,
		TableIcon
	} from '@lucide/svelte';
	import type { Component } from 'svelte';

	const navItems = [
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
					title: 'Accounts X',
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
					title: 'Audits X',
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
			url: '/settings/account',
			icon: Settings2Icon
		}
	] as Array<{
		title: string;
		url: string;
		icon: Component;
		isActive?: boolean;
		items: Array<{
			title: string;
			url: string;
		}>;
	}>;
</script>

<Sidebar.Group>
	<Sidebar.GroupLabel>Platform</Sidebar.GroupLabel>
	<Sidebar.Menu>
		{#each navItems as item (item.title)}
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
										<Sidebar.MenuSubItem>
											<Sidebar.MenuSubButton>
												{#snippet child({ props })}
													<a href={subItem.url} {...props}>
														<span>{subItem.title}</span>
													</a>
												{/snippet}
											</Sidebar.MenuSubButton>
										</Sidebar.MenuSubItem>
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
		{/each}
	</Sidebar.Menu>
</Sidebar.Group>
