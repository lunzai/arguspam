<script lang="ts">
	import * as Avatar from "$ui/avatar";
	import * as DropdownMenu from "$ui/dropdown-menu";
	import * as Sidebar from "$ui/sidebar";
	import { useSidebar } from "$ui/sidebar";
	import { auth, authLoading } from "$lib/stores/auth.store";
	import { goto } from "$app/navigation";
	import { toast } from "svelte-sonner";
	import { avatarService } from '$lib/services/avatar.service';
	
    import { BadgeCheck, Bell, ChevronsUpDown, CreditCard, LogOut, Sparkles, Loader2 } from '@lucide/svelte';

	const sidebar = useSidebar();

	// Create user data from auth store
	const userData = $derived($auth ? {
		name: $auth.name,
		email: $auth.email,
		avatar: avatarService.avatar($auth.email),
	} : {
		name: "Guest User",
		email: "guest@example.com",
		avatar: avatarService.avatar("guest@example.com"),
	});

</script>

{#if $authLoading}
	<!-- Loading state -->
	<Sidebar.Menu>
		<Sidebar.MenuItem>
			<Sidebar.MenuButton size="lg" class="opacity-50 cursor-not-allowed">
				<div class="size-8 rounded-lg bg-muted flex items-center justify-center">
					<Loader2 class="size-4 animate-spin" />
				</div>
				<div class="grid flex-1 text-left text-sm leading-tight">
					<span class="truncate font-medium text-muted-foreground">Loading...</span>
					<span class="truncate text-xs text-muted-foreground">Please wait</span>
				</div>
			</Sidebar.MenuButton>
		</Sidebar.MenuItem>
	</Sidebar.Menu>
{:else}
	<!-- User data loaded -->
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
							<Avatar.Root class="size-8 rounded-lg">
								<Avatar.Image src={userData.avatar} alt={userData.name} />
								<Avatar.Fallback class="rounded-lg">CN</Avatar.Fallback>
							</Avatar.Root>
							<div class="grid flex-1 text-left text-sm leading-tight">
								<span class="truncate font-medium">{userData.name}</span>
								<span class="truncate text-xs">{userData.email}</span>
							</div>
							<ChevronsUpDown class="ml-auto size-4" />
						</Sidebar.MenuButton>
					{/snippet}
				</DropdownMenu.Trigger>
				<DropdownMenu.Content
					class="w-(--bits-dropdown-menu-anchor-width) min-w-56 rounded-lg"
					side={sidebar.isMobile ? "bottom" : "right"}
					align="end"
					sideOffset={4}
				>
					<DropdownMenu.Label class="p-0 font-normal">
						<div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
							<Avatar.Root class="size-8 rounded-lg">
								<Avatar.Image src={userData.avatar} alt={userData.name} />
								<Avatar.Fallback class="rounded-lg">CN</Avatar.Fallback>
							</Avatar.Root>
							<div class="grid flex-1 text-left text-sm leading-tight">
								<span class="truncate font-medium">{userData.name}</span>
								<span class="truncate text-xs">{userData.email}</span>
							</div>
						</div>
					</DropdownMenu.Label>
					<DropdownMenu.Separator />
					<DropdownMenu.Group>
						<DropdownMenu.Item onSelect={() => {
							window.location.href = "/account";
						}}>
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
					<DropdownMenu.Item onSelect={handleLogout}>
						<LogOut />
						Log out
					</DropdownMenu.Item>
				</DropdownMenu.Content>
			</DropdownMenu.Root>
		</Sidebar.MenuItem>
	</Sidebar.Menu>
{/if}
