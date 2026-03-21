<script lang="ts">
    import type { Table as TableType } from "@tanstack/table-core";
    import * as Table from "$ui/table";
    import { FlexRender } from "$ui/data-table";
    import type { ColumnDef } from "@tanstack/table-core";

    type TableBodyProps = {
        table: TableType<any>;
        columns: ColumnDef<any>[];
    };

    let { table, columns }: TableBodyProps = $props();
</script>

{#each table.getRowModel().rows as row (row.id)}
    <Table.Row data-state={row.getIsSelected() && "selected"}>
    {#each row.getVisibleCells() as cell (cell.id)}
        <Table.Cell>
        <FlexRender
            content={cell.column.columnDef.cell}
            context={cell.getContext()}
        />
        </Table.Cell>
    {/each}
    </Table.Row>
{:else}
    <Table.Row>
    <Table.Cell colspan={columns.length} class="h-24 text-center">
        No results.
    </Table.Cell>
    </Table.Row>
{/each}