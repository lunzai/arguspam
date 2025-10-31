<script lang="ts">
	import * as DropdownMenu from '$ui/dropdown-menu';
	import * as Sidebar from '$ui/sidebar';
	import * as Avatar from '$ui/avatar';
	import { useSidebar } from '$ui/sidebar';
	import { ChevronsUpDown, LoaderCircle } from '@lucide/svelte';
	import { layoutStore } from '$lib/stores/layout';
	import { generateInitials, getInitials } from '$utils/avatar';
	import { toast } from 'svelte-sonner';
	import { goto, invalidate } from '$app/navigation';
	import { page } from '$app/state';

	const orgs = $derived($layoutStore.orgs);
	const currentOrgId = $derived($layoutStore.currentOrgId);
	const currentOrg = $derived($layoutStore.currentOrg);
	const sidebar = useSidebar();
	let isLoading = $state(false);

	async function onSelectOrg(orgId: number) {
		if (currentOrgId === orgId) {
			return;
		}
		isLoading = true;
		try {
			const response = await fetch(`/api/org/switch`, {
				method: 'POST',
				body: JSON.stringify({ orgId })
			});
			if (response.ok) {
				layoutStore.setCurrentOrgId(orgId);
				toast.success(`Switched organization to ${currentOrg?.name}`);
				if (page.url.pathname !== '/dashboard') {
					goto('/dashboard');
				} else {
					invalidate('dashboard:data');
				}
			} else {
				const data = await response.json();
				toast.error(data.error || 'Something went wrong');
			}
		} catch (error) {
			toast.error('Something went wrong');
		} finally {
			isLoading = false;
		}
	}
</script>

<Sidebar.Menu>
	<Sidebar.MenuItem>
		<DropdownMenu.Root>
			<DropdownMenu.Trigger disabled={isLoading}>
				{#snippet child({ props })}
					<Sidebar.MenuButton
						{...props}
						size="lg"
						class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
					>
						<div
							class="text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg"
						>
							<Avatar.Root class="size-8 rounded-lg">
								<Avatar.Image
									src={generateInitials(currentOrg?.name || '')}
									alt={currentOrg?.name || ''}
								/>
								<Avatar.Fallback class="rounded-lg"
									>{getInitials(currentOrg?.name || '')}</Avatar.Fallback
								>
							</Avatar.Root>
						</div>
						<div class="grid flex-1 text-left text-sm leading-tight">
							<span class="truncate font-medium">
								{currentOrg?.name || ''}
							</span>
						</div>
						{#if isLoading}
							<LoaderCircle class="animate-spin" />
						{:else}
							<ChevronsUpDown class="ml-auto" />
						{/if}
					</Sidebar.MenuButton>
				{/snippet}
			</DropdownMenu.Trigger>
			<DropdownMenu.Content
				class="w-(--bits-dropdown-menu-anchor-width) min-w-56 rounded-lg"
				align="start"
				side={sidebar.isMobile ? 'bottom' : 'right'}
				sideOffset={4}
			>
				<DropdownMenu.Label class="text-muted-foreground text-xs">Organizations</DropdownMenu.Label>
				{#each orgs as org, index (org.id)}
					<DropdownMenu.Item onSelect={() => onSelectOrg(org.id)} class="gap-2 p-2">
						<div class="flex size-6 items-center justify-center rounded-md border">
							<Avatar.Root class="size-6 rounded-lg">
								<Avatar.Image src={generateInitials(org.name)} alt={org.name} />
								<Avatar.Fallback class="rounded-lg">{getInitials(org.name)}</Avatar.Fallback>
							</Avatar.Root>
						</div>
						{org.name}
					</DropdownMenu.Item>
				{/each}
			</DropdownMenu.Content>
		</DropdownMenu.Root>
	</Sidebar.MenuItem>
</Sidebar.Menu>
