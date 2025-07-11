<script lang="ts">
	import { cubicInOut } from 'svelte/easing';
	import { crossfade } from 'svelte/transition';
	import { page } from '$app/stores';
	import { Button } from '$ui/button';
	import { cn } from '$lib/utils';

	let className: string | undefined | null = undefined;
	let { items }: { items: { title: string; href: string }[] } = $props();

	const [send, receive] = crossfade({
		duration: 250,
		easing: cubicInOut
	});
</script>

<nav class={cn('flex space-x-2 lg:flex-col lg:space-y-1 lg:space-x-0')}>
	{#each items as item}
		{@const isActive = $page.url.pathname === item.href}
		<Button
			href={item.href}
			variant="ghost"
			class={cn(!isActive && 'hover:underline', 'relative justify-start hover:bg-transparent')}
			data-sveltekit-noscroll
		>
			{#if isActive}
				<div
					class="bg-muted absolute inset-0 rounded-md"
					in:send={{ key: 'active-sidebar-tab' }}
					out:receive={{ key: 'active-sidebar-tab' }}
				></div>
			{/if}
			<div class="relative">
				{item.title}
			</div>
		</Button>
	{/each}
</nav>
