<script lang="ts">
	import * as DropdownMenu from '$ui/dropdown-menu';
	import * as Sidebar from '$ui/sidebar';
	import * as Avatar from '$ui/avatar';
	import { useSidebar } from '$ui/sidebar';
	import { ChevronsUpDown, LoaderCircle } from '@lucide/svelte';
	import { orgStore } from '$stores/org';
	import { generateInitials, getInitials } from '$utils/avatar';
	import { toast } from 'svelte-sonner';
	import { goto, invalidate } from '$app/navigation';
	import { page } from '$app/state';

	const orgs = $derived($orgStore.orgs);
	let currentOrgId = $derived($orgStore.currentOrgId);
	let activeOrg = $derived(orgStore.getCurrentOrg($orgStore));
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
				const org = orgs.find((o) => o.attributes.id === orgId);
				if (org) {
					toast.success(`Switched organization to ${org.attributes.name}`);
				}
				orgStore.setCurrentOrgId(orgId);
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
									src={generateInitials(activeOrg.attributes.name)}
									alt={activeOrg.attributes.name}
								/>
								<Avatar.Fallback class="rounded-lg"
									>{getInitials(activeOrg.attributes.name)}</Avatar.Fallback
								>
							</Avatar.Root>
						</div>
						<div class="grid flex-1 text-left text-sm leading-tight">
							<span class="truncate font-medium">
								{activeOrg.attributes.name}
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
				{#each orgs as org, index (org.attributes.id)}
					<DropdownMenu.Item onSelect={() => onSelectOrg(org.attributes.id)} class="gap-2 p-2">
						<div class="flex size-6 items-center justify-center rounded-md border">
							<Avatar.Root class="size-6 rounded-lg">
								<Avatar.Image
									src={generateInitials(org.attributes.name)}
									alt={org.attributes.name}
								/>
								<Avatar.Fallback class="rounded-lg"
									>{getInitials(org.attributes.name)}</Avatar.Fallback
								>
							</Avatar.Root>
						</div>
						{org.attributes.name}
					</DropdownMenu.Item>
				{/each}
			</DropdownMenu.Content>
		</DropdownMenu.Root>
	</Sidebar.MenuItem>
</Sidebar.Menu>
