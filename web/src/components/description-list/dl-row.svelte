<script lang="ts">
	import { cn, type WithElementRef } from '$lib/utils';
	import type { HTMLAttributes } from 'svelte/elements';

	let {
		ref = $bindable(null),
		children,
		class: className,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLDivElement>> = $props();
</script>

<div
	data-slot="dl-row"
	class={cn(
		'py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 lg:grid-cols-4 xl:grid-cols-5',
		className
	)}
	{...restProps}
>
	{@render children?.()}
</div>
