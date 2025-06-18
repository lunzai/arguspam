<script lang="ts">
	import * as DropdownMenu from '$ui/dropdown-menu';
	import * as Sidebar from '$ui/sidebar';
	import { useSidebar } from '$ui/sidebar';

	import { ChevronsUpDown, LogOut, IdCard } from '@lucide/svelte';
	import { goto } from '$app/navigation';
	import UserBlock from '$components/sidebar/user-block.svelte';

	const sidebar = useSidebar();
</script>

<Sidebar.Menu>
	<Sidebar.MenuItem>
		<DropdownMenu.Root>
			<DropdownMenu.Trigger>
				{#snippet child({ props })}
					<Sidebar.MenuButton
						size="lg"
						class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
						{...props}
					>
						<UserBlock />
						<ChevronsUpDown class="ml-auto size-4" />
					</Sidebar.MenuButton>
				{/snippet}
			</DropdownMenu.Trigger>
			<DropdownMenu.Content
				class="w-(--bits-dropdown-menu-anchor-width) min-w-56 rounded-lg"
				side={sidebar.isMobile ? 'bottom' : 'right'}
				align="end"
				sideOffset={4}
			>
				<DropdownMenu.Label class="p-0 font-normal">
					<div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
						<UserBlock />
					</div>
				</DropdownMenu.Label>
				<DropdownMenu.Separator />
				<DropdownMenu.Group>
					<DropdownMenu.Item
						onSelect={() => {
							goto('/settings/account');
						}}
					>
						<IdCard />
						Account
					</DropdownMenu.Item>
					<!-- <DropdownMenu.Item>
						<CreditCard />
						Billing
					</DropdownMenu.Item>
					<DropdownMenu.Item>
						<Bell />
						Notifications
					</DropdownMenu.Item> -->
				</DropdownMenu.Group>
				<DropdownMenu.Separator />
				<DropdownMenu.Item>
					<form action="/auth/logout" method="post">
						<button class="flex w-full items-center gap-2" type="submit">
							<LogOut /> Logout
						</button>
					</form>
				</DropdownMenu.Item>
			</DropdownMenu.Content>
		</DropdownMenu.Root>
	</Sidebar.MenuItem>
</Sidebar.Menu>
