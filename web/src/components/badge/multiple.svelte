<script lang="ts">
	import { Badge } from '$ui/badge';
	import { cn } from '$lib/utils';

	type Props = {
		values: string[];
		class?: string;
	};

	let { values, class: className, ...restProps }: Props = $props();
</script>

<div class="flex flex-wrap gap-1" {...restProps}>
{#each values as value}
    <Badge variant="outline" class={cn('border-gray-200 bg-gray-50 text-gray-500 capitalize', className)}>
        {value}
    </Badge>
{/each}
</div>
