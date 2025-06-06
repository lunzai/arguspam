<script lang="ts">
	import * as Avatar from '$ui/avatar';
	import * as DropdownMenu from '$ui/dropdown-menu';
	import * as Sidebar from '$ui/sidebar';
	import { useSidebar } from '$ui/sidebar';

	import { BadgeCheck, ChevronsUpDown, LogOut } from '@lucide/svelte';
	import { authService } from '$services/client/auth.js';
	import { authStore } from '$stores/auth.js';
	import { goto } from '$app/navigation';
	import { toast } from 'svelte-sonner';
	import UserBlock from '$components/sidebar/user-block.svelte';
	import { PUBLIC_AUTH_LOGIN_PATH } from '$env/static/public';

	let user = $derived({
		name: $authStore.user?.name || '',
		email: $authStore.user?.email || '',
		identifier: `${$authStore.user?.id}|${$authStore.user?.email}` || ''
	});

	const sidebar = useSidebar();
	let isLoggingOut = $state(false);

	async function handleLogout() {
		if (isLoggingOut) return;

		isLoggingOut = true;

		try {
			await authService.logout();

			// Clear auth store
			authStore.clearUser();

			// Show success message
			toast.success('Logged out successfully');

			// Redirect to login page
			await goto(PUBLIC_AUTH_LOGIN_PATH);
		} catch (error) {
			console.error('Logout error:', error);
			toast.error('Logout failed. Please try again.');
		} finally {
			isLoggingOut = false;
		}
	}
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
						<UserBlock {user} />
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
						<UserBlock {user} />
					</div>
				</DropdownMenu.Label>
				<DropdownMenu.Separator />
				<DropdownMenu.Group>
					<DropdownMenu.Item
						onSelect={() => {
							window.location.href = '/settings/account';
						}}
					>
						<BadgeCheck />
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
				<DropdownMenu.Item onSelect={handleLogout} disabled={isLoggingOut}>
					<LogOut />
					{isLoggingOut ? 'Logging out...' : 'Log out'}
				</DropdownMenu.Item>
			</DropdownMenu.Content>
		</DropdownMenu.Root>
	</Sidebar.MenuItem>
</Sidebar.Menu>
