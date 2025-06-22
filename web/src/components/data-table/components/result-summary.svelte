<script lang="ts">
	import type { PaginationConfig } from '../types';
    import { cn } from '$utils';
    import { interpolate } from '$utils/string';

	interface Props {
		pagination: PaginationConfig;
		className?: string;
		message?: string;
	}
	let { 
        pagination, 
        className = '', 
        message = 'Showing {from} to {to} of {total} results' 
    }: Props = $props();
    const summary = $derived(interpolate(message, pagination));
</script>

<div class={cn('text-sm text-gray-700', className)}>
    {summary}
</div>