<script lang="ts">
	import * as DropdownMenu from "$ui/dropdown-menu/index.js";
	import * as Sidebar from "$ui/sidebar/index.js";
	import * as Avatar from "$ui/avatar/index.js";
	import { useSidebar } from "$ui/sidebar/index.js";
	import { ChevronsUpDown, Plus } from '@lucide/svelte';

	// This should be `Component` after @lucide/svelte updates types
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	let { orgs }: { 
		orgs: { 
			name: string; 
			logo: string; 
			plan: string 
		}[] 
	} = $props();
	const sidebar = useSidebar();

	let activeOrg = $state(orgs[0]);
</script>

<Sidebar.Menu>
	<Sidebar.MenuItem>
		<DropdownMenu.Root>
			<DropdownMenu.Trigger>
				{#snippet child({ props })}
					<Sidebar.MenuButton
						{...props}
						size="lg"
						class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
					>
						<div
							class="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg"
						>
							<Avatar.Root class="size-4 rounded-lg">
								<Avatar.Image src={activeOrg.logo} alt={activeOrg.name} />
								<Avatar.Fallback class="rounded-lg">CN</Avatar.Fallback>
							</Avatar.Root>
						</div>
						<div class="grid flex-1 text-left text-sm leading-tight">
							<span class="truncate font-medium">
								{activeOrg.name}
							</span>
							<span class="truncate text-xs">{activeOrg.plan}</span>
						</div>
						<ChevronsUpDown class="ml-auto" />
					</Sidebar.MenuButton>
				{/snippet}
			</DropdownMenu.Trigger>
			<DropdownMenu.Content
				class="w-(--bits-dropdown-menu-anchor-width) min-w-56 rounded-lg"
				align="start"
				side={sidebar.isMobile ? "bottom" : "right"}
				sideOffset={4}
			>
				<DropdownMenu.Label class="text-muted-foreground text-xs">Organizations</DropdownMenu.Label>
				{#each orgs as org, index (org.name)}
					<DropdownMenu.Item onSelect={() => (activeOrg = org)} class="gap-2 p-2">
						<div class="flex size-6 items-center justify-center rounded-md border">
							<!-- <img src={org.logo} class="size-3.5 shrink-0" /> -->
							<Avatar.Root class="size-6 rounded-lg">
								<Avatar.Image src={org.logo} alt={org.name} />
								<Avatar.Fallback class="rounded-lg">CN</Avatar.Fallback>
							</Avatar.Root>
						</div>
						{org.name}
						<DropdownMenu.Shortcut>⌘{index + 1}</DropdownMenu.Shortcut>
					</DropdownMenu.Item>
				{/each}
				<DropdownMenu.Separator />
				<DropdownMenu.Item class="gap-2 p-2">
					<div
						class="flex size-6 items-center justify-center rounded-md border bg-transparent"
					>
						<Plus class="size-4" />
					</div>
					<div class="text-muted-foreground font-medium">Add Organization</div>
				</DropdownMenu.Item>
			</DropdownMenu.Content>
		</DropdownMenu.Root>
	</Sidebar.MenuItem>
</Sidebar.Menu>
