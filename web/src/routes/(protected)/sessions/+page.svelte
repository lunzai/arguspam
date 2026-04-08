<script lang="ts" >
    import type { ColumnDef } from "@tanstack/table-core";
    import { renderComponent } from "$ui/data-table";
    import { 
        Table, 
        tableStateToUrlParams, 
        AssetNameCell, 
        StartEndDurationCell, 
        SubtitleCell,
        ButtonCell
    } from '$components/datatable';
    import type { ApiMeta } from '$lib/resources/api';
    import { Status } from '$components/status';
    import { NotebookText } from '@lucide/svelte';
    import type { Table as TableType } from '@tanstack/table-core';
    import { goto } from '$app/navigation';
    import { Filter, FilterReset } from '$components/datatable';
    import { getInitialStateFromUrlParams } from '$components/datatable/helper';
    import type { SessionResource as ModelResource } from '$lib/resources/session';
    import type { Asset } from '$lib/models/asset';
    import type { User } from '$lib/models/user';
    import { page } from '$app/state';
    import { PageTitle } from '$components/page-title';

    const { data } = $props();
    const list = $derived(data?.list as ModelResource[]);
    const meta = $derived(data?.meta as ApiMeta);
    const basePath = '/sessions';
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
            id: 'scheduled_start_datetime',
            header: 'Start/End',
            cell: ({ row }) => {
                return renderComponent(StartEndDurationCell, {
                    startDatetime: row.original.attributes.scheduled_start_datetime,
                    endDatetime: row.original.attributes.scheduled_end_datetime,
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
                    subtitles: [user?.email],
                });
            }
        },
        {
            id: 'approver_id',
            header: 'Approver',
            cell: ({ row }) => {
                const user = row.original.relationships?.approver?.attributes as User;
                return renderComponent(SubtitleCell, {
                    title: user?.name,
                    subtitles: [user?.email],
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
    title="Sessions" 
    description="Monitor all active and historical privileged access sessions across database assets." 
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
            <Filter 
                table={table}
                attribute="status"
                title="Status"
                options={[
                    {
                        label: 'Scheduled',
                        value: 'scheduled',
                    },
                    {
                        label: 'Cancelled',
                        value: 'cancelled',
                    },
                    {
                        label: 'Started',
                        value: 'started',
                    },
                    {
                        label: 'Ended',
                        value: 'ended',
                    },
                    {
                        label: 'Expired',
                        value: 'expired',
                    },
                    {
                        label: 'Terminated',
                        value: 'terminated',
                    }
                ]}
            />
            <FilterReset table={table} />
        </div>
    {/snippet}
</Table>