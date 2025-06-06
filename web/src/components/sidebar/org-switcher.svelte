<script lang="ts">
	import * as DropdownMenu from '$ui/dropdown-menu/index.js';
	import * as Sidebar from '$ui/sidebar/index.js';
	import * as Avatar from '$ui/avatar/index.js';
	import { useSidebar } from '$ui/sidebar/index.js';
	import { ChevronsUpDown, LoaderCircle } from '@lucide/svelte';
	import { orgStore } from '$stores/org';
	import type { Org } from '$types/models/org';
	import { generateInitials, getInitials } from '$services/client/avatar';
	import { userService } from '$services/client/users';
	import { toast } from 'svelte-sonner';

	const orgs = $derived($orgStore.orgs);
	let currentOrgId = $orgStore.currentOrgId;
	let activeOrg = $derived(orgStore.getCurrentOrg($orgStore));
	const sidebar = useSidebar();
	let isLoading = $state(false);

	async function onSelectOrg(orgId: number) {
		if (currentOrgId === orgId) return;
		try {
			isLoading = true;
			await userService.switchOrg(orgId);
			orgStore.setCurrentOrgId(orgId);
			currentOrgId = orgId;
		} catch (error) {
			orgStore.setCurrentOrgId(currentOrgId);
			console.log('unable to swtich org', error);
			toast.error('Something went wrong. Please try again.');
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
								<Avatar.Image src={generateInitials(activeOrg.name)} alt={activeOrg.name} />
								<Avatar.Fallback class="rounded-lg">{getInitials(activeOrg.name)}</Avatar.Fallback>
							</Avatar.Root>
						</div>
						<div class="grid flex-1 text-left text-sm leading-tight">
							<span class="truncate font-medium">
								{activeOrg.name}
							</span>
							<!-- <span class="truncate text-xs">{activeOrg.plan}</span> -->
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
				{#each orgs as org, index (org.name)}
					<DropdownMenu.Item onSelect={() => onSelectOrg(org.id)} class="gap-2 p-2">
						<div class="flex size-6 items-center justify-center rounded-md border">
							<!-- <img src={org.logo} class="size-3.5 shrink-0" /> -->
							<Avatar.Root class="size-6 rounded-lg">
								<Avatar.Image src={generateInitials(org.name)} alt={org.name} />
								<Avatar.Fallback class="rounded-lg">{getInitials(org.name)}</Avatar.Fallback>
							</Avatar.Root>
						</div>
						{org.name}
					</DropdownMenu.Item>
				{/each}
				<!-- <DropdownMenu.Separator />
				<DropdownMenu.Item class="gap-2 p-2">
					<div class="flex size-6 items-center justify-center rounded-md border bg-transparent">
						<Plus class="size-4" />
					</div>
					<div class="text-muted-foreground font-medium">Add Organization</div>
				</DropdownMenu.Item> -->
			</DropdownMenu.Content>
		</DropdownMenu.Root>
	</Sidebar.MenuItem>
</Sidebar.Menu>
