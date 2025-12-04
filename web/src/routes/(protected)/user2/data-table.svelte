<script lang="ts" generics="TData">
    import { 
        type ColumnDef, 
        type PaginationState,
        type SortingState,
        type ColumnFiltersState,
        type VisibilityState,
        type Table as TableType,
        getCoreRowModel,
        getPaginationRowModel,
        getSortedRowModel,
        getFilteredRowModel,
    } from "@tanstack/table-core";
    import {
        createSvelteTable,
        FlexRender,
    } from "$ui/data-table";
    import * as Table from "$ui/table";
    import type { ApiMeta } from "$components/data-table/types";
    import { Button } from "$ui/button";
    import { 
        ChevronUp, ChevronDown, Settings2, 
        ChevronsLeftIcon, ChevronLeftIcon, 
        ChevronRightIcon, ChevronsRightIcon,
        ChevronsUpDownIcon, MoveUp, MoveDown
    } from "@lucide/svelte";
    import * as DropdownMenu from "$ui/dropdown-menu";
    import * as Select from "$ui/select";
    import { Input } from "$ui/input";

    type DataTableProps<TData> = {
        columns: ColumnDef<TData>[];
        data: TData[];
        meta: ApiMeta;
    };

    let { data, columns, meta }: DataTableProps<TData> = $props();
    let pagination = $state<PaginationState>({ pageIndex: 0, pageSize: 20 });
    let sorting = $state<SortingState>([]);
    let columnFilters = $state<ColumnFiltersState>([]);
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
        pageCount: meta.last_page,
        rowCount: meta.total,
        autoResetPageIndex: true,
        manualSorting: true,
        manualFiltering: true,
        onSortingChange: (updater) => {
            if (typeof updater === "function") {
                sorting = updater(sorting);
            } else {
                sorting = updater;
            }
            serverSort();
        },
        onPaginationChange: (updater) => {
            if (typeof updater === "function") {
                pagination = updater(pagination);
            } else {
                pagination = updater;
            }
            serverPagination();
        },
        onColumnFiltersChange: (updater) => {
            if (typeof updater === "function") {
                columnFilters = updater(columnFilters);
            } else {
                columnFilters = updater;
            }
            serverFilter();
        },
        onColumnVisibilityChange: (updater) => {
            if (typeof updater === "function") {
                columnVisibility = updater(columnVisibility);
            } else {
                columnVisibility = updater;
            }
        },
        columns,
        getCoreRowModel: getCoreRowModel(),
    });

    function serverPagination() {
        console.log('server pagination', pagination);
    }

    function serverSort() {
        console.log('server sorting', sorting);
    }

    function serverFilter() {
        console.log('server filter', columnFilters);
    }


</script>

<div class="flex items-center justify-between">
    <div>
        <Input type="text" placeholder="Search..." />
    </div>
    <div>
        <DropdownMenu.Root>
            <DropdownMenu.Trigger>
                {#snippet child({ props })}
                    <Button {...props} variant="outline" class="ms-auto">
                        <Settings2 class="h-4 w-4" />
                        Columns
                    </Button>
                {/snippet}
            </DropdownMenu.Trigger>
            <DropdownMenu.Content align="end" class="min-w-56">
                {#each table
                    .getAllColumns()
                    .filter((col) => col.getCanHide()) as column (column.id)}
                    <DropdownMenu.CheckboxItem
                    class="capitalize"
                    bind:checked={
                        () => column.getIsVisible(), (v) => column.toggleVisibility(!!v)
                    }
                    >
                    {column.columnDef.header}
                    </DropdownMenu.CheckboxItem>
                {/each}
            </DropdownMenu.Content>
        </DropdownMenu.Root>
    </div>
</div>


<div class="rounded-md border">
    <Table.Root>
        <Table.Header>
        {#each table.getHeaderGroups() as headerGroup (headerGroup.id)}
            <Table.Row>
            {#each headerGroup.headers as header (header.id)}
                <Table.Head colspan={header.colSpan}>
                {#if !header.isPlaceholder}
                    <button
                        class:cursor-pointer={header.column.getCanSort()}
                        class:select-none={!header.column.getCanSort()}
                        onclick={header.column.getToggleSortingHandler()}
                        class="flex items-center gap-1"
                    >
                        <FlexRender
                            content={header.column.columnDef.header}
                            context={header.getContext()}
                        />
                        {#if header.column.getCanSort()}
                            {#if header.column.getIsSorted().toString() === 'asc'}
                                <MoveUp class="h-4 w-4 text-gray-300" />
                            {:else if header.column.getIsSorted().toString() === 'desc'}
                                <MoveDown class="h-4 w-4 text-gray-300" />
                            {:else}
                                <ChevronsUpDownIcon class="h-4 w-4 text-gray-300" />
                            {/if}
                        {/if}
                    </button>
                {/if}
                </Table.Head>
            {/each}
            </Table.Row>
        {/each}
        </Table.Header>
        <Table.Body>
        {#each table.getRowModel().rows as row (row.id)}
            <Table.Row data-state={row.getIsSelected() && "selected"}>
            {#each row.getVisibleCells() as cell (cell.id)}
                <Table.Cell>
                <FlexRender
                    content={cell.column.columnDef.cell}
                    context={cell.getContext()}
                />
                </Table.Cell>
            {/each}
            </Table.Row>
        {:else}
            <Table.Row>
            <Table.Cell colspan={columns.length} class="h-24 text-center">
                No results.
            </Table.Cell>
            </Table.Row>
        {/each}
        </Table.Body>
    </Table.Root>
</div>
{@render Pagination({ table })}

{#snippet Pagination({ table }: { table: TableType<TData> })}
    <div class="flex items-center justify-between px-2">
        <div class="text-muted-foreground flex-1 text-sm">
            {#if meta.total > 0}
                {meta.from} to {meta.to} of {meta.total} rows.
            {/if}
        </div>
        <div class="flex items-center space-x-6 lg:space-x-8">
            <div class="flex items-center space-x-2">
                <p class="text-sm font-medium">Rows per page</p>
                <Select.Root
                    allowDeselect={false}
                    type="single"
                    value={`${table.getState().pagination.pageSize}`}
                    onValueChange={(value) => {
                        table.setPageSize(Number(value));
                    }}
                >
                    <Select.Trigger class="h-8 w-[70px]">
                        {String(table.getState().pagination.pageSize)}
                    </Select.Trigger>
                    <Select.Content side="top">
                        {#each [10, 20, 30, 40, 50] as pageSize (pageSize)}
                            <Select.Item value={`${pageSize}`}>
                                {pageSize}
                            </Select.Item>
                        {/each}
                    </Select.Content>
                </Select.Root>
            </div>
            <div class="flex w-[100px] items-center justify-center text-sm font-medium">
                Page {table.getState().pagination.pageIndex + 1} of
                {table.getPageCount()}
            </div>
            <div class="flex items-center space-x-2">
                <Button
                    variant="outline"
                    class="hidden size-8 p-0 lg:flex"
                    onclick={() => table.setPageIndex(0)}
                    disabled={!table.getCanPreviousPage()}
                >
                    <span class="sr-only">Go to first page</span>
                    <ChevronsLeftIcon />
                </Button>
                <Button
                    variant="outline"
                    class="size-8 p-0"
                    onclick={() => table.previousPage()}
                    disabled={!table.getCanPreviousPage()}
                >
                    <span class="sr-only">Go to previous page</span>
                    <ChevronLeftIcon />
                </Button>
                <Button
                    variant="outline"
                    class="size-8 p-0"
                    onclick={() => table.nextPage()}
                    disabled={!table.getCanNextPage()}
                >
                    <span class="sr-only">Go to next page</span>
                    <ChevronRightIcon />
                </Button>
                <Button
                    variant="outline"
                    class="hidden size-8 p-0 lg:flex"
                    onclick={() => table.setPageIndex(table.getPageCount() - 1)}
                    disabled={!table.getCanNextPage()}
                >
                    <span class="sr-only">Go to last page</span>
                    <ChevronsRightIcon />
                </Button>
            </div>
        </div>
    </div>
{/snippet}