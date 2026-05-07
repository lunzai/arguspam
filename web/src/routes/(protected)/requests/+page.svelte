<script lang="ts">
	import { shortDateTime } from '$utils/date';
    import type { ColumnDef } from "@tanstack/table-core";
    import { renderComponent } from "$ui/data-table";
    import { 
        Table, 
        tableStateToUrlParams, 
        AssetNameCell, 
        StartEndDurationCell, 
        TextWrapCell,
        SubtitleCell,
        ButtonCell
    } from '$components/datatable';
    import type { ApiMeta } from '$lib/resources/api';
    import { Status } from '$components/status';
    import { NotebookText } from '@lucide/svelte';
    import type { Table as TableType } from '@tanstack/table-core';
    import { goto } from '$app/navigation';
    import { Filter, FilterReset } from '$components/datatable';
    import { page } from '$app/state';
    import { getInitialStateFromUrlParams } from '$components/datatable/helper';
    import type { RequestResource as ModelResource } from '$lib/resources/request';
    import type { Asset } from '$lib/models/asset';
    import type { User } from '$lib/models/user';
    import { PageTitle } from '$components/page-title';

    const { data } = $props();
    const list = $derived(data?.list as ModelResource[]);
    const meta = $derived(data?.meta as ApiMeta);
    const basePath = '/requests';
    const baseParams = {};
    const { initialSorting, initialFilters } = getInitialStateFromUrlParams(page.url, ['status']);

    const columns: ColumnDef<ModelResource>[] = [
        {
            id: 'id',
            header: 'ID',
            accessorKey: 'attributes.id',
        },
        {
            id: 'asset_id',
            header: 'Asset',
            cell: ({ row }) => {
				return renderComponent(AssetNameCell, {
					asset: row.original.relationships?.asset?.attributes as Asset,
				});
			},
        },
        {
            id: 'start_datetime',
            header: 'Start/End',
            cell: ({ row }) => {
                return renderComponent(StartEndDurationCell, {
                    startDatetime: row.original.attributes.start_datetime,
                    endDatetime: row.original.attributes.end_datetime,
                });
            }
        },
        {
            id: 'reason',
            header: 'Reason',
            accessorKey: 'attributes.reason',
            cell: ({ row }) => {
                return renderComponent(TextWrapCell, {
                    text: row.original.attributes.reason,
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
            id: 'requester_id',
            header: 'Requester',
            cell: ({ row }) => {
                const user = row.original.relationships?.requester?.attributes as User;
                return renderComponent(SubtitleCell, {
                    title: user?.name,
                    subtitles: [user?.email, row.original.attributes.created_at ? shortDateTime(row.original.attributes.created_at) : '-'],
                });
            }
        },
        {
            id: 'approver_id',
            header: 'Approver',
            cell: ({ row }) => {
                let user: User | null = null;
                let datetime: Date | null = null;
                if (row.original.attributes.approved_at) {
                    user = row.original.relationships?.approver?.attributes as User;
                    datetime = row.original.attributes.approved_at;
                }
                if (row.original.attributes.rejected_at) {
                    user = row.original.relationships?.rejecter?.attributes as User;
                    datetime = row.original.attributes.rejected_at;
                }
                if (!user) {
                    return '-';
                }
                return renderComponent(SubtitleCell, {
                    title: user?.name,
                    subtitles: [user?.email, datetime ? shortDateTime(datetime) : '-'],
                });
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
    title="Requests" 
    description="Review and manage privileged access windows across all production environments. Automated risk scoring is applied to every request." 
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
        <div class="flex gap-2 items-center">
            <Filter 
                table={table}
                attribute="status"
                title="Status"
                options={[
                    {
                        label: 'Pending',
                        value: 'pending',
                    },
                    {
                        label: 'Submitted',
                        value: 'submitted',
                    },
                    {
                        label: 'Approved',
                        value: 'approved',
                    },
                    {
                        label: 'Rejected',
                        value: 'rejected',
                    },
                    {
                        label: 'Expired',
                        value: 'expired',
                    },
                    {
                        label: 'Cancelled',
                        value: 'cancelled',
                    }
                ]}
            />
            <FilterReset table={table} />
        </div>
    {/snippet}
</Table>