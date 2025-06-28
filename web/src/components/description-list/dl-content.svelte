<script lang="ts">
	import { cn, type WithElementRef } from '$lib/utils';
	import type { HTMLAttributes } from 'svelte/elements';

	let {
		children,
		class: className,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLElement>> = $props();
</script>

<dd
	data-slot="dl-content"
	class={cn('text-sm text-gray-700 sm:col-span-2 sm:mt-0 lg:col-span-3 xl:col-span-4', className)}
	{...restProps}
>
	{@render children?.()}
</dd>
