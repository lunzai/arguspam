<script lang="ts">
	import { cn } from '$lib/utils';

	let {
		children,
		className
	}: {
		children?: () => any;
		className?: string;
	} = $props();
</script>

<ol class={cn('relative space-y-12 border-s border-gray-300', className)}>
	{@render children?.()}
</ol>
