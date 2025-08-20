<script lang="ts">
	import { cn, type WithElementRef } from '$lib/utils';
	import type { HTMLAttributes } from 'svelte/elements';

	type Props = {
		orientation?: 'horizontal' | 'vertical';
	} & WithElementRef<HTMLAttributes<HTMLDivElement>>;

	let {
		ref = $bindable(null),
		children,
		class: className,
		orientation = 'horizontal',
		...restProps
	}: Props = $props();
</script>

<div
	data-slot="dl-row"
	class={cn(
		orientation === 'horizontal'
			? 'py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 lg:grid-cols-4 xl:grid-cols-5'
			: 'flex flex-col gap-1.5',
		className
	)}
	{...restProps}
>
	{@render children?.()}
</div>
