<script lang="ts">
	import { page } from '$app/state';
	import { Separator } from '$ui/separator';
	import SidebarNav from '$components/page-sidebar/sidebar.svelte';
	import type { User } from '$models/user';
	import type { ApiUserResource } from '$resources/user';
	import type { LayoutData } from './$types';

	let { children, data }: { children: any; data: LayoutData } = $props();
	const {
		canUserResetPasswordAny,
		canUserEnrollTwoFactorAuthenticationAny,
		canUserUpdateAny,
		canUserViewAny
	} = data;
	const modelResource = $state(page.data.model as ApiUserResource);
	const model = $state(modelResource.data.attributes as User);

	let sidebarNavItems: { title: string; href: string }[] = [
		// {
		// 	title: 'Assets',
		// 	href: `/users/${model.id}/assets`
		// },
		// {
		// 	title: 'Requests',
		// 	href: `/users/${model.id}/requests`
		// },
		// {
		// 	title: 'Audit',
		// 	href: `/users/${model.id}/audit`
		// }
	];

	if (canUserViewAny) {
		sidebarNavItems.push({
			title: 'Profile',
			href: `/users/${model.id}`
		});
	}
	if (canUserResetPasswordAny || canUserEnrollTwoFactorAuthenticationAny) {
		sidebarNavItems.push({
			title: 'Security',
			href: `/users/${model.id}/security`
		});
	}
</script>

<div class="flex flex-col space-y-4">
	<h1 class="text-xl font-medium capitalize">User #{model.id} - {model.name}</h1>
	<Separator />
	<div class="mt-2 flex flex-col gap-6 lg:flex-row">
		<aside class="w-48">
			<SidebarNav items={sidebarNavItems} />
		</aside>
		<div class="flex-1">
			{@render children()}
		</div>
	</div>
</div>
