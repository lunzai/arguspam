<script lang="ts">
    import type { Table as TableType } from "@tanstack/table-core";
    import * as Table from "$ui/table";
    import { FlexRender } from "$ui/data-table";
    import { MoveUp, MoveDown, ChevronsUpDownIcon } from "@lucide/svelte";

    type TableHeaderProps = {
        table: TableType<any>;
    };

    let { table }: TableHeaderProps = $props();
</script>

{#each table.getHeaderGroups() as headerGroup (headerGroup.id)}
    <Table.Row class="bg-slate-50">
        {#each headerGroup.headers as header (header.id)}
            <Table.Head colspan={header.colSpan} class="rounded-t-lg px-4 pt-4 pb-3 text-[11px] font-bold">
                {#if !header.isPlaceholder}
                    <button
                        class:cursor-pointer={header.column.getCanSort()}
                        class:select-none={!header.column.getCanSort()}
                        onclick={header.column.getToggleSortingHandler()}
                        class="flex items-center gap-1 uppercase tracking-widest text-slate-800"
                    >
                        <FlexRender
                            content={header.column.columnDef.header}
                            context={header.getContext()}
                        />
                        {#if header.column.getCanSort()}
                            {#if header.column.getIsSorted().toString() === 'asc'}
                                <MoveUp class="h-4 w-4 text-gray-300" />
                            {:else if header.column.getIsSorted().toString() === 'desc'}
                                <MoveDown class="h-4 w-4 text-gray-300" />
                            {:else}
                                <ChevronsUpDownIcon class="h-4 w-4 text-gray-300" />
                            {/if}
                        {/if}
                    </button>
                {/if}
            </Table.Head>
        {/each}
    </Table.Row>
{/each}