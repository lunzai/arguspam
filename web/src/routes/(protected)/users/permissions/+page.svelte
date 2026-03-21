<script lang="ts">
    import { shortDateTime } from '$utils/date';
    import type { ColumnDef } from "@tanstack/table-core";
    import type { PermissionResource as ModelResource } from '$lib/resources/permission';
    import { renderComponent } from "$ui/data-table";
    import { Table, tableStateToUrlParams, ButtonCell } from '$components/datatable';
    import type { ApiMeta } from '$lib/resources/api';
    import { NotebookText } from '@lucide/svelte';
    import type { Table as TableType } from '@tanstack/table-core';
    import { goto } from '$app/navigation';
    import { Search, FilterReset } from '$components/datatable';
    import { getInitialStateFromUrlParams } from '$components/datatable';
    import { page } from '$app/state';

    const { data } = $props();
    const list = $derived(data?.list as ModelResource[]);
    const meta = $derived(data?.meta as ApiMeta);
    const basePath = '/users/permissions';
    const baseParams = {};
    const { initialSorting, initialFilters } = getInitialStateFromUrlParams(page.url, []);

    const columns: ColumnDef<ModelResource>[] = [
        {
            id: 'id',
            header: 'ID',
            accessorKey: 'attributes.id',
        },
        {
            id: 'name',
            header: 'Name',
            accessorKey: 'attributes.name',
        },
        {
            id: 'description',
            header: 'Description',
            accessorKey: 'attributes.description',
        },
        {
            id: 'created_at',
            header: 'Created At',
            accessorKey: 'attributes.created_at',
            enableColumnFilter: false,
            cell: ({ row }) => {
                return row.original.attributes.created_at ? shortDateTime(row.original.attributes.created_at) : '-';
            }
        },
        {
            id: 'actions',
            header: 'Actions',
            enableHiding: false,
            enableColumnFilter: false,
            cell: ({ row }) => {
                return renderComponent(ButtonCell, {
                    href: `${basePath}/${row.original.attributes.id}`,
                    label: 'View',
                    icon: NotebookText,
                });
            }
        }
    ];

    function handlePaginationChange(table: TableType<ModelResource>) {
        const params = tableStateToUrlParams(table, baseParams);
        goto(`${basePath}?${params.toString()}`);
    }

    function handleSortChange(table: TableType<ModelResource>) {
        const params = tableStateToUrlParams(table, baseParams);
        goto(`${basePath}?${params.toString()}`);
    }

    function handleFilterChange(table: TableType<ModelResource>) {
        const params = tableStateToUrlParams(table, baseParams);
        goto(`${basePath}?${params.toString()}`);
    }

    function handleColumnVisibilityChange(table: TableType<ModelResource>) {
        // Column visibility is local-only; no server round-trip
    }
</script>

<h1 class="text-2xl font-medium capitalize">Permissions</h1>

<Table 
    columns={columns} 
    data={list as ModelResource[]} 
    meta={meta as ApiMeta} 
    onPaginationChange={handlePaginationChange} 
    onSortingChange={handleSortChange} 
    onColumnFiltersChange={handleFilterChange} 
    onColumnVisibilityChange={handleColumnVisibilityChange} 
    {initialSorting}
    {initialFilters}
>
    {#snippet filters(table: TableType<ModelResource>)}
        <div class="flex gap-2">
            <Search 
                table={table}
                attribute="name"
                title="Name"
            />

            <Search 
                table={table}
                attribute="description"
                title="description"
            />
            
            <FilterReset table={table} />
        </div>
    {/snippet}
</Table>