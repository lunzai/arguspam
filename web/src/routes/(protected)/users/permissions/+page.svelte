<script lang="ts">
    import { shortDateTime } from '$utils/date';
    import type { ColumnDef } from "@tanstack/table-core";
    import type { PermissionResource } from '$lib/resources/permission';
    import { renderComponent } from "$ui/data-table";
    import { Table, tableStateToUrlParams } from '$components/datatable';
    import type { ApiMeta } from '$components/data-table/types';
    import DatatableButton from '$components/datatable/button.svelte';
    import { NotebookText } from '@lucide/svelte';
    import type { Table as TableType } from '@tanstack/table-core';
    import { goto } from '$app/navigation';
    import { Search, FilterReset } from '$components/datatable';

    const { data } = $props();
    const list = $derived(data?.list as PermissionResource[]);
    const meta = $derived(data?.meta as ApiMeta);
    const basePath = '/users/permissions';
    const baseParams = {};

    const columns: ColumnDef<PermissionResource>[] = [
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
                return renderComponent(DatatableButton, {
                    href: `/${basePath}/${row.original.attributes.id}`,
                    label: 'View',
                    icon: NotebookText,
                });
            }
        }
    ];

    function handlePaginationChange(table: TableType<PermissionResource>) {
        const params = tableStateToUrlParams(table, baseParams);
        goto(`${basePath}?${params.toString()}`);
    }

    function handleSortChange(table: TableType<PermissionResource>) {
        const params = tableStateToUrlParams(table, baseParams);
        goto(`${basePath}?${params.toString()}`);
    }

    function handleFilterChange(table: TableType<PermissionResource>) {
        const params = tableStateToUrlParams(table, baseParams);
        goto(`${basePath}?${params.toString()}`);
    }

    function handleColumnVisibilityChange(table: TableType<PermissionResource>) {
        // Column visibility is local-only; no server round-trip
    }
</script>

<h1 class="text-2xl font-medium capitalize">Permissions</h1>

<Table 
    columns={columns} 
    data={list as PermissionResource[]} 
    meta={meta as ApiMeta} 
    onPaginationChange={handlePaginationChange} 
    onSortingChange={handleSortChange} 
    onColumnFiltersChange={handleFilterChange} 
    onColumnVisibilityChange={handleColumnVisibilityChange} 
>
    {#snippet filters(table: TableType<PermissionResource>)}
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