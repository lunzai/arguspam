<script lang="ts">
	import { page } from '$app/state';
	import { Separator } from '$ui/separator';
	import SidebarNav from '$components/page-sidebar/sidebar.svelte';
	import type { User } from '$models/user';
	import type { UserResource } from '$resources/user';
	
	let { 
		children,
	} = $props();
	const modelResource = $state(page.data.model as UserResource);
	const model = $state(modelResource.data.attributes as User);
	
	const sidebarNavItems = [
		{
			title: 'Profile',
			href: `/users/${model.id}`
		},
		{
			title: 'Security',
			href: `/users/${model.id}/security`
		},
        {
			title: 'Restrictions',
			href: `/users/${model.id}/restrictions`
		},
        {
			title: 'Assets',
			href: `/users/${model.id}/assets`
		},
        {
			title: 'Requests',
			href: `/users/${model.id}/requests`
		},
        {
			title: 'Audit',
			href: `/users/${model.id}/audit`
		},
	];
</script>

<div class="flex flex-col space-y-4">
    <h1 class="text-xl font-medium capitalize">User #{model.id} - {model.name}</h1>
    <Separator />
    <div
        class="flex flex-col lg:flex-row mt-2 gap-8"
    >
        <aside class="w-48">
            <SidebarNav items={sidebarNavItems} />
        </aside>
        <div class="flex-1">
            {@render children()}
        </div>
    </div>
</div>
