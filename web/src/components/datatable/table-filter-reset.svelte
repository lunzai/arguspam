

<script lang="ts" generics="TData, TValue">
    import type { Table as TableType } from "@tanstack/table-core";
    import { Button } from "$ui/button";
    import XIcon from "@lucide/svelte/icons/x";

    let { table }: { table: TableType<TData> } = $props();
    const isFiltered = $derived(table.getState().columnFilters.length > 0);
</script>

{#if isFiltered}
    <Button
        variant="ghost"
        onclick={() => table.resetColumnFilters()}
        class="h-8 px-2 lg:px-3"
    >
        Reset
        <XIcon />
    </Button>
{/if}