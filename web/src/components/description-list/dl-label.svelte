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
	class={cn('sm:col-span- text-sm font-medium text-gray-900', className)}
	{...restProps}
>
	{@render children?.()}
</dt>
