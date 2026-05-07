<script lang="ts">
	import type { Role } from '$models/role';
	import { shortDateTime } from '$lib/utils/date';
	import { page } from '$app/state';
	import { NotebookText, PlusIcon } from '@lucide/svelte';
	import { Button } from '$ui/button';
	import FormDialog from './form-dialog.svelte';
	import { goto } from '$app/navigation';
    import { HoverCardCell } from '$components/datatable';
    import { Status } from '$components/status';
    import { renderComponent } from "$ui/data-table";
    import type { Table as TableType } from '@tanstack/table-core';
    import { Table, tableStateToUrlParams, ButtonCell } from '$components/datatable';
    import { Filter, FilterReset, Search } from '$components/datatable';
    import type { ColumnDef } from "@tanstack/table-core";
    import { getInitialStateFromUrlParams } from '$components/datatable/helper';
    import type { RoleResource as ModelResource } from '$lib/resources/role';
    import type { ApiMeta } from '$lib/resources/api';
    import { PageTitle } from '$components/page-title';

	let { data }: { data: any } = $props();
    const list = $derived(data?.list as ModelResource[]);
    const meta = $derived(data?.meta as ApiMeta);
    const basePath = '/users/roles';
    const baseParams = {};
    const { initialSorting, initialFilters } = getInitialStateFromUrlParams(page.url, ['status']);
	let addRoleDialogIsOpen = $state(false);

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
            id: 'is_default',
            header: 'Default Role',
            accessorKey: 'attributes.is_default',
            cell: ({ row }) => {
                return renderComponent(Status, { status: row.original.attributes.is_default ? 'Yes' : 'No' });
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
    title="Roles" 
    description="Manage your roles and their configurations." 
>
    <Button
        variant="outline"
        class="gap-2 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500 bg-white"
        onclick={() => {
            addRoleDialogIsOpen = true;
        }}
    >
        <PlusIcon class="h-4 w-4" />
        <span>Add Role</span>
    </Button>
</PageTitle>

<FormDialog
	bind:isOpen={addRoleDialogIsOpen}
	model={data.model}
	data={data.form}
	onSuccess={async (data: Role) => {
		await goto(`${basePath}/${data.id}`);
		addRoleDialogIsOpen = false;
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
                attribute="is_default"
                title="Default Role"
                options={[
                    {
                        label: 'Yes',
                        value: '1',
                    },
                    {
                        label: 'No',
                        value: '0',
                    }
                ]}
            />
            <FilterReset table={table} />
        </div>
    {/snippet}
</Table>