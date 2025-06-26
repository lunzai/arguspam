<script lang="ts">
    import { cn } from '$lib/utils';
    import { Badge } from '$ui/badge';
    import type { CellBadge } from '../types';

    interface Props {
        values?: CellBadge[];
        className?: string;
    }
    let { values = [], className = '' }: Props = $props();
</script>

{#if values.length > 0}
    <div class={cn('flex flex-wrap gap-1', className)}>
        {#each values as cellBadge}
            <Badge variant={cellBadge.variant} class={cn('badge', cellBadge.className)}>{cellBadge.value}</Badge>
        {/each}
    </div>
{/if}