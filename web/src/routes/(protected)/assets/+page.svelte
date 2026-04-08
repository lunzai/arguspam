<script lang="ts">
	import type { Asset } from '$models/asset';
	import { shortDateTime } from '$lib/utils/date';
	import { page } from '$app/state';
	import { NotebookText, PlusIcon } from '@lucide/svelte';
	import { Button } from '$ui/button';
	import FormDialog from './form-dialog.svelte';
	import { goto } from '$app/navigation';
    import type { ColumnDef } from "@tanstack/table-core";
    import { renderComponent } from "$ui/data-table";
    import { 
        Table, 
        tableStateToUrlParams, 
        Search,
        ButtonCell,
    } from '$components/datatable';
    import type { ApiMeta } from '$lib/resources/api';
    import { Status } from '$components/status';
    import type { Table as TableType } from '@tanstack/table-core';
    import { Filter, FilterReset } from '$components/datatable';
    import { getInitialStateFromUrlParams } from '$components/datatable/helper';
    import type { AssetResource as ModelResource } from '$lib/resources/asset';
    import { HoverCardCell } from '$components/datatable';
    import { PageTitle } from '$components/page-title';

    const { data } = $props();
    const list = $derived(data?.list as ModelResource[]);
    const meta = $derived(data?.meta as ApiMeta);
    const basePath = '/assets';
    const baseParams = {};
    const { initialSorting, initialFilters } = getInitialStateFromUrlParams(page.url, ['status']);
    let addAssetDialogIsOpen = $state(false);

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
            cell: ({ row }) => {
                return renderComponent(HoverCardCell, {
                    triggerLabel: row.original.attributes.name,
                    hoverContent: row.original.attributes.description,
                });
            }
        },
        {
            id: 'status',
            header: 'Status',
            accessorKey: 'attributes.status',
            cell: ({ row }) => {
                return renderComponent(Status, { status: row.original.attributes.status });
            }
        },
        {
            id: 'dbms',
            header: 'DBMS',
            accessorKey: 'attributes.dbms',
            cell: ({ row }) => {
                return row.original.attributes.dbms ? row.original.attributes.dbms.toUpperCase() : '-';
            }
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
            id: 'updated_at',
            header: 'Updated At',
            accessorKey: 'attributes.updated_at',
            enableColumnFilter: false,
            cell: ({ row }) => {
                return row.original.attributes.updated_at ? shortDateTime(row.original.attributes.updated_at) : '-';
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

<PageTitle 
    title="Assets" 
    description="Manage your database assets and their configurations." 
>
    <Button
        variant="outline"
        class="gap-2 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500 bg-white"
        onclick={() => {
            addAssetDialogIsOpen = true;
        }}
    >
        <PlusIcon class="h-4 w-4" />
        <span>Add Asset</span>
    </Button>
</PageTitle>

<FormDialog
	bind:isOpen={addAssetDialogIsOpen}
	model={data.model as Asset}
	data={data.form}
	onSuccess={async (data: Asset) => {
		await goto(`${basePath}/${data.id}`);
		addAssetDialogIsOpen = false;
	}}
/>

<Table 
    columns={columns} 
    data={list as ModelResource[]} 
    meta={meta as ApiMeta} 
    onPaginationChange={handlePaginationChange} 
    onSortingChange={handleSortChange} 
    onColumnFiltersChange={handleFilterChange} 
    onColumnVisibilityChange={handleColumnVisibilityChange} 
    {initialFilters}
    {initialSorting}
>
    {#snippet filters(table: TableType<ModelResource>)}
        <div class="flex gap-2">
            <Search 
                table={table}
                attribute="name"
                title="Name"
            />
            <Filter 
                table={table}
                attribute="status"
                title="Status"
                options={[
                    {
                        label: 'Active',
                        value: 'active',
                    },
                    {
                        label: 'Inactive',
                        value: 'inactive',
                    }
                ]}
            />
            <Filter 
                table={table}
                attribute="dbms"
                title="DBMS"
                options={[
                    {
                        label: 'MySQL',
                        value: 'mysql',
                    },
                    {
                        label: 'PostgreSQL',
                        value: 'postgresql',
                    },
                    {
                        label: 'SQL Server',
                        value: 'sqlserver',
                    },
                    {
                        label: 'Oracle',
                        value: 'oracle',
                    },
                    {
                        label: 'MongoDB',
                        value: 'mongodb',
                    },
                    {
                        label: 'Redis',
                        value: 'redis',
                    },
                    {
                        label: 'MariaDB',
                        value: 'mariadb',
                    }
                ]}
            />
            <FilterReset table={table} />
        </div>
    {/snippet}
</Table>