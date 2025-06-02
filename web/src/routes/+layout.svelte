<script lang="ts">
	import '../app.css';
	import { page } from '$app/stores';
	import { Toaster } from "$ui/sonner";
	import LogoutButton from '$components/auth/logout-button.svelte';
	let { children, data } = $props();
</script>

<Toaster richColors position="top-center" />

{#if data.isAuthenticated}
	<div class="flex h-screen bg-background">
		<!-- Admin Layout -->
		<aside class="flex flex-col bg-white shadow-lg w-64">
			<div class="p-4 border-b">
				<img src="/logo.png" alt="ArgusPAM" class="h-8 w-auto" />
			</div>
			<nav class="flex-1 p-4">
				<!-- Navigation content -->
				<p class="text-sm text-gray-600">Navigation will go here</p>
			</nav>
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
	<div class="min-h-screen bg-background">
		<!-- Guest Layout -->
		{@render children()}
	</div>
{/if}
