<script lang="ts">
	import '../../app.css';
	import { page } from '$app/stores';
	import { ChevronDown, ChevronRight, Users, UserCog, Shield, Building2, 
			 Settings, FileText, Clock, Package, UserCircle, LogOut } from '@lucide/svelte';

	let { children } = $props();
	let isExpanded = true;
	let expandedMenus: Record<string, boolean> = {};

	const navigation = [
		{
			name: 'Users',
			icon: Users,
			href: '/users',
			submenu: [
				{ name: 'All Users', href: '/users' },
				{ name: 'User Groups', href: '/user-groups' },
				{ name: 'Roles', href: '/roles' },
				{ name: 'Permissions', href: '/permissions' }
			]
		},
		{
			name: 'Organizations',
			icon: Building2,
			href: '/orgs'
		},
		{
			name: 'Assets',
			icon: Package,
			href: '/assets'
		},
		{
			name: 'Settings',
			icon: Settings,
			href: '/settings'
		},
		{
			name: 'Audits',
			icon: FileText,
			href: '/audits',
			submenu: [
				{ name: 'System Audits', href: '/audits' },
				{ name: 'Session Audits', href: '/session-audits' }
			]
		}
	];

	function toggleMenu(menuName: string) {
		expandedMenus[menuName] = !expandedMenus[menuName];
		expandedMenus = expandedMenus;
	}

	function getBreadcrumbs() {
		const path = $page.url.pathname;
		return path.split('/').filter(Boolean).map(segment => ({
			name: segment.charAt(0).toUpperCase() + segment.slice(1).replace(/-/g, ' '),
			href: '/' + path.split('/').slice(1, path.split('/').indexOf(segment) + 1).join('/')
		}));
	}
</script>

<div class="flex h-screen bg-gray-100">
	<!-- Sidebar -->
	<aside class="flex flex-col bg-white shadow-lg" class:w-64={isExpanded} class:w-16={!isExpanded}>
		<!-- Logo -->
		<div class="flex items-center justify-between p-4 border-b">
			{#if isExpanded}
				<span class="text-xl font-bold">Admin</span>
			{/if}
			<button 
				class="p-1 rounded hover:bg-gray-100"
				on:click={() => isExpanded = !isExpanded}
			>
				{isExpanded ? '<' : '>'}
			</button>
		</div>

		<!-- Navigation -->
		<nav class="flex-1 overflow-y-auto">
			{#each navigation as item}
				<div class="py-2">
					<button
						class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100"
						class:justify-center={!isExpanded}
						on:click={() => item.submenu ? toggleMenu(item.name) : null}
					>
						<svelte:component this={item.icon} class="w-5 h-5" />
						{#if isExpanded}
							<span class="ml-3">{item.name}</span>
							{#if item.submenu}
								<svelte:component 
									this={expandedMenus[item.name] ? ChevronDown : ChevronRight} 
									class="w-4 h-4 ml-auto"
								/>
							{/if}
						{/if}
					</button>

					{#if item.submenu && expandedMenus[item.name] && isExpanded}
						<div class="pl-12">
							{#each item.submenu as subItem}
								<a
									href={subItem.href}
									class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-100"
									class:bg-gray-100={$page.url.pathname === subItem.href}
								>
									{subItem.name}
								</a>
							{/each}
						</div>
					{/if}
				</div>
			{/each}
		</nav>

		<!-- User Menu -->
		<div class="p-4 border-t">
			<button class="flex items-center w-full" class:justify-center={!isExpanded}>
				<UserCircle class="w-5 h-5" />
				{#if isExpanded}
					<span class="ml-3">User Name</span>
					<LogOut class="w-4 h-4 ml-auto" />
				{/if}
			</button>
		</div>
	</aside>

	<!-- Main Content -->
	<main class="flex-1 overflow-y-auto">
		<!-- Header -->
		<header class="bg-white shadow">
			<div class="px-4 py-6">
				<h1 class="text-2xl font-semibold text-gray-900">
					{$page.url.pathname.split('/').pop()?.replace(/-/g, ' ') || 'Dashboard'}
				</h1>
				<!-- Breadcrumbs -->
				<nav class="flex mt-2">
					{#each getBreadcrumbs() as crumb, i}
						<a 
							href={crumb.href}
							class="text-sm text-gray-500 hover:text-gray-700"
						>
							{crumb.name}
						</a>
						{#if i < getBreadcrumbs().length - 1}
							<span class="mx-2 text-gray-400">/</span>
						{/if}
					{/each}
				</nav>
			</div>
		</header>

		<!-- Page Content -->
		<div class="p-6">
			{@render children()}
		</div>
	</main>
</div>
