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
    import type { ApiMeta } from "$lib/resources/api";
    import * as DataTable from "$components/datatable/index";
    import type { Snippet } from "svelte";

    type DataTableProps<TData> = {
        columns: ColumnDef<TData>[];
        data: TData[];
        meta: ApiMeta;
        filters?: Snippet<[TableType<TData>]>;
        onPaginationChange: (table: TableType<TData>) => void;
        onSortingChange: (table: TableType<TData>) => void;
        onColumnFiltersChange: (table: TableType<TData>) => void;
        onColumnVisibilityChange: (table: TableType<TData>) => void;
        initialSorting?: SortingState;
        initialFilters?: ColumnFiltersState;
    };

    let { 
        data, 
        columns, 
        meta, 
        filters,
        onPaginationChange, 
        onSortingChange, 
        onColumnFiltersChange, 
        onColumnVisibilityChange,
        initialSorting,
        initialFilters
    }: DataTableProps<TData> = $props();

    let pagination = $state<PaginationState>({ 
        pageIndex: meta?.current_page ? meta?.current_page - 1 : 0, 
        pageSize: meta?.per_page ?? 20 
    });
    let sorting = $state<SortingState>(initialSorting ?? []);
    let columnFilters = $state<ColumnFiltersState>(initialFilters ?? []);
    let columnVisibility = $state<VisibilityState>({});

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
            table.resetPageIndex();
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
    {#if filters}
        <div>
            {@render filters(table)}
        </div>
    {/if}
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