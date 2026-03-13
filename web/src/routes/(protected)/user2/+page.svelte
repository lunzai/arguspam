<script lang="ts">
	import type { User } from '$models/user';
	import { shortDateTime } from '$utils/date';
    import type { ColumnDef } from "@tanstack/table-core";
	import type { UserResource } from '$lib/resources/user';
	import type { RoleResource } from '$lib/resources/role';
    import { renderComponent } from "$ui/data-table";
    import { Table, tableStateToUrlParams } from '$components/datatable';
    import type { ApiMeta } from '$components/data-table/types';
    import { Status } from '$components/status';
	import { MultipleBadge } from '$components/badge';
    import DatatableButton from '$components/datatable/button.svelte';
    import { NotebookText } from '@lucide/svelte';
    import type { Table as TableType, VisibilityState } from '@tanstack/table-core';
    import { goto } from '$app/navigation';

    const { data } = $props();
    const list = $derived(data?.list as UserResource[]);
    const meta = $derived(data?.meta as ApiMeta);

    const columns: ColumnDef<UserResource>[] = [
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
            id: 'mfa',
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
                return renderComponent(DatatableButton, {
                    href: `/users/${row.original.attributes.id}`,
                    label: 'View',
                    icon: NotebookText,
				});
			}
		}
	];

    const basePath = '/user2';
    const baseParams = {};

    function handlePaginationChange(table: TableType<UserResource>) {
        const params = tableStateToUrlParams(table, baseParams);
        goto(`${basePath}?${params.toString()}`);
    }

    function handleSortChange(table: TableType<UserResource>) {
        const params = tableStateToUrlParams(table, baseParams);
        goto(`${basePath}?${params.toString()}`);
    }

    function handleFilterChange(table: TableType<UserResource>) {
        const params = tableStateToUrlParams(table, baseParams);
        goto(`${basePath}?${params.toString()}`);
    }

    function handleColumnVisibilityChange(table: TableType<UserResource>) {
        // Column visibility is local-only; no server round-trip
    }
</script>

<Table 
    columns={columns} 
    data={list as UserResource[]} 
    meta={meta as ApiMeta} 
    onPaginationChange={handlePaginationChange} 
    onSortingChange={handleSortChange} 
    onColumnFiltersChange={handleFilterChange} 
    onColumnVisibilityChange={handleColumnVisibilityChange} 
/>