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
						class="bg-white rounded-lg shadow-xs flex items-center gap-3 px-3 py-2 border-outline-variant/10 cursor-pointer hover:bg-slate-50 transition-colors"
					>
						<div
							class="text-sidebar-primary-foreground flex aspect-square size-8 rounded-lg items-center justify-center"
						>
							<Avatar.Root class="size-8">
								<Avatar.Image
									src={generateInitials(currentOrg?.name || '')}
									alt={currentOrg?.name || ''}
								/>
								<Avatar.Fallback class=""
									>{getInitials(currentOrg?.name || '')}</Avatar.Fallback
								>
							</Avatar.Root>
						</div>
						<div class="grid flex-1 text-left text-xs tracking-tight uppercase">
							<span class="truncate font-bold">
								{currentOrg?.name || ''}
							</span>
						</div>
						{#if isLoading}
							<LoaderCircle class="animate-spin text-slate-400" />
						{:else}
							<ChevronsUpDown class="ml-auto text-slate-400" />
						{/if}
					</Sidebar.MenuButton>
				{/snippet}
			</DropdownMenu.Trigger>
			<DropdownMenu.Content
				class="w-(--bits-dropdown-menu-anchor-width) min-w-72 rounded-lg px-3 pt-2 pb-3"
				align="start"
				side={sidebar.isMobile ? 'bottom' : 'right'}
				sideOffset={4}
			>
				<DropdownMenu.Label class="text-muted-foreground text-xs py-2">Organizations</DropdownMenu.Label>
				{#each orgs as org, index (org.id)}
					<DropdownMenu.Item onSelect={() => onSelectOrg(org.id)} class="gap-2 p-2">
						<div class="flex size-6 items-center justify-center rounded border">
							<Avatar.Root class="size-6 rounded">
								<Avatar.Image src={generateInitials(org.name)} alt={org.name} />
								<Avatar.Fallback class="rounded">{getInitials(org.name)}</Avatar.Fallback>
							</Avatar.Root>
						</div>
						<span class="text-xs tracking-tight font-medium uppercase truncate">{org.name}</span>
					</DropdownMenu.Item>
				{/each}
			</DropdownMenu.Content>
		</DropdownMenu.Root>
	</Sidebar.MenuItem>
</Sidebar.Menu>
