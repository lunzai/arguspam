<script lang="ts">
	import type { User } from '$models/user';
	import { shortDateTime } from '$utils/date';
	import { page } from '$app/state';
    import type { ColumnDef } from "@tanstack/table-core";
	import type { UserResource } from '$lib/resources/user';
	import type { RoleResource } from '$lib/resources/role';
    import { renderComponent, renderSnippet } from "$ui/data-table";
    import DataTable from './data-table.svelte';
    import type { ApiMeta, Resource } from '$components/data-table/types';
    import type { User as UserModel } from '$models/user';
    import { Status } from '$components/status';
	import { MultipleBadge } from '$components/badge';
    import DatatableButton from '$components/datatable/button.svelte';
    import { NotebookText } from '@lucide/svelte';

    const { data } = $props();
    const {
        list,
        meta
    } = data;

    const columnsFilterType = [
        {
            id: 'id',
            type: 'number',
        },
        {
            id: 'name',
            type: 'text',
        },
        {
            id: 'email',
            type: 'text',
        },
        {
            id: 'roles',
            type: 'select',
            options: [
                {
                    label: 'Admin',
                    value: 'admin',
                },
                {
                    label: 'User',
                    value: 'user',
                },
            ],
        },
        {
            id: 'mfa',
            type: 'select',
            options: [
                {
                    label: 'Enrolled',
                    value: 'enrolled',
                },
                {
                    label: 'Pending',
                    value: 'pending',
                },
                {
                    label: 'Off',
                    value: 'off',
                },
            ],
        },
        {
            id: 'status',
            type: 'select',
            options: [
                {
                    label: 'Active',
                    value: 'active',
                },
                {
                    label: 'Inactive',
                    value: 'inactive',
                },
            ],
        },
    ];
    
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
                // You can pass whatever you need from `row.original` to the component
                return renderComponent(DatatableButton, {
                    href: `/users/${row.original.attributes.id}`,
                    label: 'View',
                    icon: NotebookText,
				});
			}
		}
	];
</script>

<DataTable {columns} data={list as UserResource[]} meta={meta as ApiMeta} />
