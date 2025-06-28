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
	class={cn('border-green-200 bg-green-50 text-green-500 capitalize', className)}
	{...restProps}
>
	{@render children?.()}
</Badge>
