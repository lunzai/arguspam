<script lang="ts">
	import { Separator } from '$ui/separator';
	import SidebarNav from '$components/page-sidebar/sidebar.svelte';

	let { children } = $props();

	const sidebarNavItems = [
		{
			title: 'Account',
			href: '/settings/account'
		},
		{
			title: 'Security',
			href: '/settings/security'
		}
	];
</script>

<div class="flex flex-col space-y-4">
	<h1 class="text-xl font-medium capitalize">Settings</h1>
	<Separator />
	<div class="mt-2 flex flex-col gap-8 lg:flex-row">
		<aside class="w-48">
			<SidebarNav items={sidebarNavItems} />
		</aside>
		<div class="flex-1">
			{@render children()}
		</div>
	</div>
</div>
