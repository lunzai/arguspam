<script lang="ts">
	import { shortDateTime } from '$utils/date';
    import type { ColumnDef } from "@tanstack/table-core";
	import type { UserResource as ModelResource } from '$lib/resources/user';
	import type { RoleResource } from '$lib/resources/role';
    import { renderComponent } from "$ui/data-table";
    import { Table, tableStateToUrlParams, ButtonCell } from '$components/datatable';
    import type { ApiMeta } from '$lib/resources/api';
    import { Status } from '$components/status';
	import { MultipleBadge } from '$components/badge';
    import { NotebookText } from '@lucide/svelte';
    import type { Table as TableType } from '@tanstack/table-core';
    import { goto } from '$app/navigation';
    import { Filter, Search, FilterReset } from '$components/datatable';
    import { page } from '$app/state';
    import { getInitialStateFromUrlParams } from '$components/datatable/helper';

    const { data } = $props();
    const list = $derived(data?.list as ModelResource[]);
    const meta = $derived(data?.meta as ApiMeta);
    const basePath = '/users';
    const baseParams = {};
    const { initialSorting, initialFilters } = getInitialStateFromUrlParams(page.url, ['status', 'two_factor_enabled']);

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
            id: 'email',
            header: 'Email',
            accessorKey: 'attributes.email',
        },
        {
            id: 'roles',
            header: 'Roles',
            cell: ({ row }) => {
                return renderComponent(MultipleBadge, { 
                    values: row.original.relationships?.roles?.map((role: RoleResource) => role.attributes.name) || [],
                    class: 'bg-transparent text-gray-700',
                });
            }
        },
        {
            id: 'two_factor_enabled',
            header: 'MFA',
            accessorKey: 'attributes.two_factor_enabled',
            cell: ({ row }) => {
                return renderComponent(Status, { 
                    status: row.original.attributes.two_factor_enabled ? 
                        row.original.attributes.two_factor_confirmed_at ? 'Active' : 'Pending' : 'Off'
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
            id: 'last_login_at',
            header: 'Last Login At',
            accessorKey: 'attributes.last_login_at',
            enableColumnFilter: false,
            cell: ({ row }) => {
                return row.original.attributes.last_login_at ? shortDateTime(row.original.attributes.last_login_at) : '-';
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

<h1 class="text-2xl font-medium capitalize">Users</h1>

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

            <Search 
                table={table}
                attribute="email"
                title="Email"
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
                attribute="two_factor_enabled"
                title="MFA"
                options={[
                    {
                        label: 'Active',
                        value: 'active',
                    },
                    {
                        label: 'Pending',
                        value: 'pending',
                    },
                    {
                        label: 'Off',
                        value: 'off',
                    }
                ]}
            />
            <FilterReset table={table} />
        </div>
    {/snippet}
</Table>