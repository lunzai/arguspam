<script lang="ts">
	import type { Component } from 'svelte';
	import { cn } from '$lib/utils';

	let {
		icon: Icon,
		title,
		description,
		color = 'gray',
		disabled = false
	}: {
		icon?: Component;
		title?: string;
		description?: string;
		color: 'red' | 'green' | 'blue' | 'gray' | 'yellow';
		disabled?: boolean;
	} = $props();

	const colorMap = {
		red: 'bg-red-200 ring-red-100 text-red-500',
		green: 'bg-green-200 ring-green-100 text-green-500',
		blue: 'bg-blue-200 ring-blue-100 text-blue-500',
		gray: 'bg-gray-200 ring-gray-100 text-gray-500',
		yellow: 'bg-yellow-200 ring-yellow-100 text-yellow-500'
	};

	const disabledClass = disabled ? 'bg-gray-100! ring-gray-50! text-gray-300!' : '';
</script>

<li class="ms-8" class:cursor-not-allowed={disabled}>
	<span
		class={cn(
			'absolute -start-4 flex h-8 w-8 items-center justify-center rounded-full ring-4',
			colorMap[color],
			disabledClass
		)}
	>
		{#if Icon}
			<Icon class="h-3.5 w-3.5" />
		{/if}
	</span>
	{#if title}
		<h3
			class="leading-tight font-medium {disabled ? 'text-gray-200 line-through' : 'text-gray-800'}"
		>
			{title}
		</h3>
	{/if}
	{#if description}
		<p class="text-sm {disabled ? 'text-gray-200 line-through' : 'text-gray-500'}">{description}</p>
	{/if}
</li>
