<script lang="ts">
	import '../app.css';
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
		<main class="flex-1 overflow-y-auto">
			{@render children()}
		</main>
	</div>
{:else}
	<div class="bg-background min-h-screen">
		<!-- Guest Layout -->
		{@render children()}
	</div>
{/if}
