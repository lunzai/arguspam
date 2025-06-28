<script lang="ts">
	import { Badge } from '$ui/badge';
	import { cn } from '$lib/utils';

	type Props = {
		status: string;
		label?: string | null;
		class?: string;
	};

	let { status, label = null, class: className, ...restProps }: Props = $props();

	const badgeClass = cn({
		'text-green-500 bg-green-50 border-green-200': status === 'active',
		'text-gray-500 bg-gray-50 border-gray-200': status === 'inactive'
	});
</script>

<Badge variant="outline" class={cn('capitalize', badgeClass, className)} {...restProps}>
	{label || status}
</Badge>
