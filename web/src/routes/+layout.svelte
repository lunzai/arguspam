<script lang="ts">
	import '../app.css';
	import { page } from '$app/state';
	import { Toaster } from '$ui/sonner';
	let { children } = $props();
</script>

<Toaster richColors position="top-center" duration={5000} theme="light" />

<svelte:head>
	<title>
		ArgusPAM{page.data.title ? ` - ${page.data.title}` : ''}
	</title>
	<meta name="description" content={page.data.description} />
</svelte:head>

<div class="bg-background min-h-screen">
	{@render children()}
</div>