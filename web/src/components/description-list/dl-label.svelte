<script lang="ts">
	import { cn, type WithElementRef } from '$lib/utils';
	import type { HTMLAttributes } from 'svelte/elements';

	let {
		ref = $bindable(null),
		children,
		class: className,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLElement>> = $props();
</script>

<dt
	data-slot="dl-label"
	class={cn('text-sm font-medium text-gray-900 sm:col-span-1', className)}
	{...restProps}
>
	{@render children?.()}
</dt>
