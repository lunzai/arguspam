<script lang="ts">
	import { cn } from '$lib/utils';

	type Props = {
		ref?: HTMLElement | null;
		children: () => any;
		divider?: string | null;
		class?: string;
		dlClass?: string;
	};

	let {
		ref = $bindable(null),
		children,
		divider = 'divide-gray-100',
		class: className,
		dlClass,
		...restProps
	}: Props = $props();
</script>

<div
	bind:this={ref}
	data-slot="description-list"
	class={cn('border-gray-100', className)}
	{...restProps}
>
	<dl class={cn(divider ? `divide-y ${divider}` : '', dlClass)}>
		{@render children?.()}
	</dl>
</div>
