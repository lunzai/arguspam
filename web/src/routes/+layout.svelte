<script lang="ts">
	import '../app.css';
	import { page } from '$app/stores';
	import { Toaster } from "$ui/sonner";
	let { children } = $props();
</script>

<Toaster richColors position="top-center" />

{#if $page.data.isAuthenticated}
	<div class="flex h-screen bg-background">
		<!-- Admin Layout -->
		<aside class="flex flex-col bg-white shadow-lg w-64">
			<!-- Navigation content -->
		</aside>
		<main class="flex-1 overflow-y-auto">
			{@render children()}
		</main>
	</div>
{:else}
	<div class="min-h-screen bg-background">
		<!-- Guest Layout -->
		{@render children()}
	</div>
{/if}
