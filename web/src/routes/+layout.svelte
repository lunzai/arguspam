<script lang="ts">
	import { page } from '$app/stores';
	import { Toaster } from '$ui/sonner';
	let { children } = $props();
</script>

<Toaster richColors position="top-center" />

{#if $page.data.isAuthenticated}
	<div class="bg-background flex h-screen">
		<!-- Admin Layout -->
		<aside class="flex w-64 flex-col bg-white shadow-lg">
			<!-- Navigation content -->
		</aside>
		<main class="flex-1 flex flex-col overflow-hidden">
			<!-- Header -->
			<header class="bg-white shadow-sm border-b px-6 py-4">
				<div class="flex items-center justify-between">
					<h1 class="text-lg font-semibold text-gray-900">
						Welcome, {data.user?.name || 'User'}
					</h1>
					<div class="flex items-center gap-4">
						<span class="text-sm text-gray-600">{data.user?.email}</span>
						<LogoutButton />
					</div>
				</div>
			</header>
			<!-- Main content -->
			<div class="flex-1 overflow-y-auto p-6">
				{@render children()}
			</div>
		</main>
	</div>
{:else}
	<div class="bg-background min-h-screen">
		<!-- Guest Layout -->
		{@render children()}
	</div>
{/if}
