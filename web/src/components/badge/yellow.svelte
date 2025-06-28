<script lang="ts">
	import { Badge } from '$ui/badge';
	import { cn } from '$lib/utils';

	type Props = {
		children: () => any;
		class?: string;
	};

	let { children, class: className, ...restProps }: Props = $props();
</script>

<Badge
	variant="outline"
	class={cn('border-yellow-200 bg-yellow-50 text-yellow-500 capitalize', className)}
	{...restProps}
>
	{@render children?.()}
</Badge>
