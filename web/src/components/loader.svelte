<script lang="ts">
	import { Loader2 } from '@lucide/svelte';
	import { cn } from '$lib/utils';

	interface Props {
		show: boolean;
		children?: () => any;
		class?: string;
	}

	let { show = $bindable(false), children, class: className }: Props = $props();
</script>

{#if show}
	<div
		class={cn(
			'absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-gray-50/50 transition-all',
			className
		)}
	>
		{#if children}
			{@render children()}
		{:else}
			<Loader2 class="h-8 w-8 animate-spin text-gray-300" />
		{/if}
	</div>
{/if}
