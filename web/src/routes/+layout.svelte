<script lang="ts">
	import '../app.css';
	import { page } from '$app/stores';
	let { children } = $props();
</script>

{#if $page.data.isAuthenticated}
	<div class="flex h-screen bg-gray-100">
		<!-- Admin Layout -->
		<aside class="flex flex-col bg-white shadow-lg w-64">
			<!-- Navigation content -->
		</aside>
		<main class="flex-1 overflow-y-auto">
			{@render children()}
		</main>
	</div>
{:else}
	<div class="min-h-screen bg-gray-100">
		<!-- Guest Layout -->
		{@render children()}
	</div>
{/if}
