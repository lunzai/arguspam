<script lang="ts">
    import { createSvelteTable } from "$ui/data-table";
    import type { AssetResource } from "$lib/resources/asset";
    import { 
        type ColumnDef,
        type PaginationState, 
        type SortingState, 
        type ColumnFiltersState, 
        type VisibilityState,
        getCoreRowModel,
        getFilteredRowModel,
        getPaginationRowModel,
    } from "@tanstack/table-core";
    import * as Card from '$ui/card';
	import { Database, ArrowRight, ChevronsLeftIcon, ChevronLeftIcon, ChevronRightIcon, ChevronsRightIcon } from "@lucide/svelte";
	import { Badge } from "$lib/components/ui/badge";
	import { cn } from "$lib/utils";
	import Status from "$components/status/status.svelte";
	import { Button } from "$ui/button";
	import type { Asset } from "$lib/models/asset";
    import {
        FilterReset,
        Search,
        Filter,
    } from "$components/datatable";

    let { 
        list,
        onRequestAccess
    }: { 
        list: Asset[],
        onRequestAccess: (asset: Asset) => void
    } = $props();
    let pagination = $state<PaginationState>({ pageIndex: 0, pageSize: 12 });
    let columnFilters = $state<ColumnFiltersState>([]);
    let globalFilter = $state<string>();

    const columns: ColumnDef<Asset>[] = [
        {
            id: 'id',
            header: 'ID',
            accessorKey: 'id',
        },
        {
            id: 'name',
            header: 'Name',
            accessorKey: 'name',
        },
        {
            id: 'description',
            header: 'Description',
            accessorKey: 'description',
        },
        {
            id: 'status',
            header: 'Status',
            filterFn: 'arrIncludesSome',
            accessorKey: 'status',
        },
        {
            id: 'dbms',
            header: 'DBMS',
            filterFn: (row, columnId, filterValue) => {
                return filterValue.some((value: string) => row.original.dbms.toLowerCase().includes(value.toLowerCase()));
            },
            accessorKey: 'dbms',
        },
        {
            id: 'host',
            header: 'Host',
            accessorKey: 'host',
        },
        {
            id: 'port',
            header: 'Port',
            accessorKey: 'port',
        },
        {
            id: 'created_at',
            header: 'Created At',
            enableGlobalFilter: false,
            accessorKey: 'created_at',
        },
        {
            id: 'updated_at',
            header: 'Updated At',
            enableGlobalFilter: false,
            accessorKey: 'updated_at',
        },
    ];

    const table = createSvelteTable({
        get data() {
            return list;
        },
        state: {
            get pagination() {
                return pagination;
            },
            get columnFilters() {
                return columnFilters;
            },
            get globalFilter() {
                return globalFilter;
            },
        },
        onPaginationChange: (updater) => {
            if (typeof updater === "function") {
                pagination = updater(pagination);
            } else {
                pagination = updater;
            }
        },
        onColumnFiltersChange: (updater) => {
            if (typeof updater === "function") {
                columnFilters = updater(columnFilters);
            } else {
                columnFilters = updater;
            }
        },
        onGlobalFilterChange: (updater) => {
            if (typeof updater === "function") {
                globalFilter = updater(globalFilter);
            } else {
                globalFilter = updater;
            }
            console.log(globalFilter);
        },
        columns,
        autoResetPageIndex: true,
        globalFilterFn: 'includesString',
        getCoreRowModel: getCoreRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
    });

    function getDbmsColor(dbms: string) {
        switch (dbms.toLowerCase()) {
            case 'mysql':
                return 'bg-blue-100 text-blue-500';
            case 'postgresql':
                return 'bg-green-100 text-green-500';
            case 'sqlserver':
                return 'bg-red-100 text-red-500';
            case 'oracle':
                return 'bg-purple-100 text-purple-500';
            case 'mongodb':
                return 'bg-yellow-100 text-yellow-500';
            case 'redis':
                return 'bg-orange-100 text-orange-500';
            case 'mariadb':
                return 'bg-pink-100 text-pink-500';
            default:
                return 'bg-gray-100 text-gray-500';
        }
    }
</script>

<div class="flex gap-2 items-center">
    <input
        class="bg-white py-1 h-auto min-h-10 px-2 rounded-md border border-slate-200"
        type="text"
        bind:value={globalFilter}
        oninput={(e) => table.setGlobalFilter(String((e.target as HTMLInputElement).value))}
        placeholder="Search..."
    />
    <Filter
        table={table}
        attribute="dbms"
        title="DBMS"
        options={[
            { label: 'MySQL', value: 'mysql' },
            { label: 'PostgreSQL', value: 'postgresql' },
            { label: 'SQL Server', value: 'sqlserver' },
            { label: 'Oracle', value: 'oracle' },
            { label: 'MongoDB', value: 'mongodb' },
            { label: 'Redis', value: 'redis' },
            { label: 'MariaDB', value: 'mariadb' },
        ]}
    />
    <Filter
        table={table}
        attribute="status"
        title="Status"
        options={[
            { label: 'Active', value: 'active' },
            { label: 'Inactive', value: 'inactive' },
        ]}
    />
    <FilterReset
        table={table}
    />
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 gap-y-6">
    {#each table.getRowModel().rows as row (row.id)}
        <Card.Root class="@container/card rounded-xl shadow-none border-0 hover:shadow-[0_20px_50px_-12px_rgba(23,28,35,0.08)]">
            <Card.Content class="px-6 grow">
                <div class="flex flex-col gap-6">
                    <div class="flex justify-between items-center gap-2">
                        <div class="rounded-lg bg-slate-100 p-3">
                            <Database strokeWidth={2.5} class="h-6 w-6" />
                        </div>
                        <Badge class={cn('text-[10px] font-extrabold uppercase tracking-widest py-1 px-3 rounded-full', getDbmsColor(row.original.dbms))}>
                            {row.original.dbms}
                        </Badge>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2 group-hover:text-primary transition-colors">
                            {row.original.name}
                        </h3>
                        <div class="flex items-center gap-1">
                            <Badge variant="secondary" class="bg-slate-100 text-slate-500">ID: #{row.original.id}</Badge>
                            <Badge variant="secondary" class="bg-slate-100 text-slate-500">{row.original.host}{#if row.original.port}
                                    :{row.original.port}
                                {/if}
                            </Badge>
                        </div>
                    </div>
                    <span>{row.original.description}</span>
                    
                </div>
            </Card.Content>
            <Card.Footer>
                <div class="flex items-center justify-between w-full gap-2">
                    <Status status={row.original.status} />
                    <Button 
                        variant="ghost" 
                        class="transition-all font-bold duration-200 hover:bg-blue-50 hover:text-blue-500 cursor-pointer"
                        onclick={() => onRequestAccess(row.original)}
                    >
                        Request Access <ArrowRight strokeWidth={2.5} class="h-4 w-4" />
                    </Button>
                </div>
            </Card.Footer>
        </Card.Root>
    {/each}
</div>

{#if table.getRowCount() > 0}
    <div class="flex items-center justify-between mt-6">
        <div class="flex-1 text-xs uppercase tracking-widest font-bold">
            {table.getState().pagination.pageIndex * table.getState().pagination.pageSize + 1} to {table.getState().pagination.pageIndex * table.getState().pagination.pageSize + table.getRowModel().rows.length} of {table.getRowCount()} rows.
        </div>
        <div class="flex items-center space-x-6 lg:space-x-8">
            <div class="flex w-[100px] items-center justify-center text-xs font-bold uppercase tracking-widest">
                Page {table.getState().pagination.pageIndex + 1} of
                {table.getPageCount()}
            </div>
            <div class="flex items-center space-x-2">
                <Button
                    variant="outline"
                    class="hidden size-8 p-0 lg:flex bg-white"
                    onclick={() => table.firstPage()}
                    disabled={!table.getCanPreviousPage()}
                >
                    <span class="sr-only">Go to first page</span>
                    <ChevronsLeftIcon />
                </Button>
                <Button
                    variant="outline"
                    class="size-8 p-0 bg-white"
                    onclick={() => table.previousPage()}
                    disabled={!table.getCanPreviousPage()}
                >
                    <span class="sr-only">Go to previous page</span>
                    <ChevronLeftIcon />
                </Button>
                <Button
                    variant="outline"
                    class="size-8 p-0 bg-white"
                    onclick={() => table.nextPage()}
                    disabled={!table.getCanNextPage()}
                >
                    <span class="sr-only">Go to next page</span>
                    <ChevronRightIcon />
                </Button>
                <Button
                    variant="outline"
                    class="hidden size-8 p-0 lg:flex bg-white"
                    onclick={() => table.lastPage()}
                    disabled={!table.getCanNextPage()}
                >
                    <span class="sr-only">Go to last page</span>
                    <ChevronsRightIcon />
                </Button>
            </div>
        </div>
    </div>
{:else}
    <div class="flex items-center justify-center h-full">
        <p class="text-slate-300 text-sm font-bold uppercase tracking-widest">No matching assets found.</p>
    </div>
{/if}