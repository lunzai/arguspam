<script lang="ts">
	import { Separator } from '$ui/separator';
	import SidebarNav from '$components/page-sidebar/sidebar.svelte';
	import { PageTitle } from '$components/page-title';

	let { children, data } = $props();

	const { canUpdateProfile, canChangePassword, canEnrollTwoFactor } = data;

	let sidebarNavItems = [];
	if (canUpdateProfile) {
		sidebarNavItems.push({
			title: 'Account',
			href: '/settings/account'
		});
	}
	if (canChangePassword || canEnrollTwoFactor) {
		sidebarNavItems.push({
			title: 'Security',
			href: '/settings/security'
		});
	}
</script>

<div class="flex flex-col space-y-4">

    <PageTitle 
        title="Settings" 
        description="Manage your account and security settings." 
    />

	<Separator />
	<div class="mt-2 flex flex-col gap-8 lg:flex-row">
		<aside class="w-48">
			<SidebarNav items={sidebarNavItems} />
		</aside>
		<div class="min-w-0 flex-1">
			{@render children()}
		</div>
	</div>
</div>
