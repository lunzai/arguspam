<script lang="ts">
	import { page } from '$app/state';
	import { Separator } from '$ui/separator';
	import SidebarNav from '$components/page-sidebar/sidebar.svelte';
	import type { User } from '$models/user';
	import type { ApiUserResource } from '$resources/user';
	import type { LayoutData } from './$types';
	import { PageTitle } from '$components/page-title';
	import * as ButtonGroup from '$ui/button-group';
	import { StatusBadge } from '$components/badge';
	import { Button } from '$ui/button';
	import { Pencil, Trash2 } from '@lucide/svelte';

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
    
    <div class="flex flex-col space-y-2">
        <div class="flex flex-row space-x-2 items-center">
            <StatusBadge status={model.status} />
            <div class="text-sm text-slate-500 uppercase tracking-wider font-semibold">
                USER #{model.id}
            </div>
        </div>
        <PageTitle
            title={model.name}
            class=""
        >
            <div class="flex justify-between gap-2">
                <!-- <ButtonGroup.Root>
                    {#if canUpdate}
                        <Button
                            variant="outline"
                            class="transition-all duration-200 h-auto py-2.5 px-4! bg-white hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500"
                            onclick={() => (editOrgDialogIsOpen = true)}
                        >
                            <Pencil class="h-4 w-4" />
                            Edit
                        </Button>
                    {/if}
                    {#if canDelete}
                        <Button
                            variant="outline"
                            class="transition-all duration-200 h-auto py-2.5 px-4! bg-white hover:border-red-200 hover:bg-red-50 hover:text-red-500"
                            onclick={() => (deleteOrgDialogIsOpen = true)}
                        >
                            <Trash2 class="h-4 w-4" />
                            Delete
                        </Button>
                    {/if}
                </ButtonGroup.Root> -->
            </div>
        </PageTitle>
        
        <Separator />
    </div>

	<div class="mt-2 flex flex-col gap-6 lg:flex-row">
		<aside class="w-48">
			<SidebarNav items={sidebarNavItems} />
		</aside>
		<div class="flex-1">
			{@render children()}
		</div>
	</div>
</div>
