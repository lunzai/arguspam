<script lang="ts">
	import { page } from '$app/state';
	import { Separator } from '$ui/separator';
	import SidebarNav from '$components/settings-sidebar/sidebar.svelte';

	const sidebarNavItems = [
		{
			title: 'Account',
			href: '/settings/account'
		},
		{
			title: 'Security',
			href: '/settings/security'
		},
		// {
		// 	title: 'Preferences',
		// 	href: '/settings/preferences'
		// }
	];
</script>

<h1 class="text-2xl font-medium">Settings</h1>
<Separator class="my-4" />
<div class="flex flex-col space-y-8  lg:max-w-5xl xl:max-w-7xl lg:flex-row lg:space-x-12 lg:space-y-0">
	<aside class="w-48">
		<SidebarNav items={sidebarNavItems} />
	</aside>
	<div class="flex-1">
		<slot />
	</div>
</div>