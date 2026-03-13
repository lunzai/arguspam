<script lang="ts" generics="TData">
    import { 
        type ColumnDef, 
        type PaginationState,
        type SortingState,
        type ColumnFiltersState,
        type VisibilityState,
        type Table as TableType,
        getCoreRowModel,
    } from "@tanstack/table-core";
    import {
        createSvelteTable,
    } from "$ui/data-table";
    import * as Table from "$ui/table";
    import type { ApiMeta } from "$components/data-table/types";
    import * as DataTable from "$components/datatable/index";

    type DataTableProps<TData> = {
        columns: ColumnDef<TData>[];
        data: TData[];
        meta: ApiMeta;
        onPaginationChange: (table: TableType<TData>) => void;
        onSortingChange: (table: TableType<TData>) => void;
        onColumnFiltersChange: (table: TableType<TData>) => void;
        onColumnVisibilityChange: (table: TableType<TData>) => void;
    };

    let { 
        data, 
        columns, 
        meta, 
        onPaginationChange, 
        onSortingChange, 
        onColumnFiltersChange, 
        onColumnVisibilityChange 
    }: DataTableProps<TData> = $props();
    
    let pagination = $state<PaginationState>({ 
        pageIndex: meta?.current_page ? meta?.current_page - 1 : 0, 
        pageSize: meta?.per_page ?? 20 
    });
    let sorting = $state<SortingState>([]);
    let columnFilters = $state<ColumnFiltersState>([]);
    let columnVisibility = $state<VisibilityState>({});

    $inspect('meta', meta);

    const table = createSvelteTable({
        get data() {
            return data;
        },
        state: {
            get pagination() {
                return pagination;
            },
            get sorting() {
                return sorting;
            },
            get columnFilters() {
                return columnFilters;
            },
            get columnVisibility() {
                return columnVisibility;
            },
        },
        manualPagination: true,
        get pageCount() {
            return meta?.last_page ?? -1;
        },
        get rowCount() {
            return meta?.total ?? -1;
        },
        // autoResetPageIndex: true,
        manualSorting: true,
        manualFiltering: true,
        onSortingChange: (updater) => {
            if (typeof updater === "function") {
                sorting = updater(sorting);
            } else {
                sorting = updater;
            }
            onSortingChange(table);
        },
        onPaginationChange: (updater) => {
            if (typeof updater === "function") {
                pagination = updater(pagination);
            } else {
                pagination = updater;
            }
            onPaginationChange(table);
        },
        onColumnFiltersChange: (updater) => {
            if (typeof updater === "function") {
                columnFilters = updater(columnFilters);
            } else {
                columnFilters = updater;
            }
            onColumnFiltersChange(table);
        },
        onColumnVisibilityChange: (updater) => {
            if (typeof updater === "function") {
                columnVisibility = updater(columnVisibility);
            } else {
                columnVisibility = updater;
            }
            onColumnVisibilityChange(table);
        },
        columns,
        getCoreRowModel: getCoreRowModel()
    });
</script>

<div class="flex items-center justify-between">
    <div>
        <!-- <Input type="text" placeholder="Search..." /> -->
    </div>
    <div>
        <DataTable.ColumnSelector table={table} />
    </div>
</div>

<div class="rounded-md border">
    <Table.Root>
        <Table.Header>
            <DataTable.TableHeader table={table} />
        </Table.Header>
        <Table.Body>
            <DataTable.TableBody table={table} columns={columns} />
        </Table.Body>
    </Table.Root>
</div>
<DataTable.Pagination table={table} meta={meta} />