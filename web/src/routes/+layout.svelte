<script lang="ts">
	import '../app.css';
	import { page } from '$app/state';
	import { Toaster } from '$ui/sonner';
	let { children } = $props();
</script>

<Toaster richColors position="top-center" duration={5000} />

<svelte:head>
	<title>
		ArgusPAM{page.data.title ? ` - ${page.data.title}` : ''}
	</title>
	<meta name="description" content={page.data.description} />
</svelte:head>

<div class="bg-background min-h-screen">
	{@render children()}
</div>

<!-- {#if page.data.isAuthenticated}
	<div class="bg-background flex h-screen">
		<!-- Admin Layout --
		<aside class="flex w-64 flex-col bg-white shadow-lg">
			<!-- Navigation content --
		</aside>
		<main class="flex-1 overflow-y-auto">
			{@render children()}
		</main>
	</div>
{:else}
	<div class="bg-background min-h-screen">
		<!-- Guest Layout --
		{@render children()}
	</div>
{/if} -->
