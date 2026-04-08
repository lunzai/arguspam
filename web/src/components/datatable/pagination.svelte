<script lang="ts">
    import type { Table as TableType } from "@tanstack/table-core";
    import * as Select from "$ui/select";
    import { Button } from "$ui/button";
    import { ChevronsLeftIcon, ChevronLeftIcon, ChevronRightIcon, ChevronsRightIcon } from "@lucide/svelte";
    import type { ApiMeta } from "$lib/resources/api";

    type PaginationProps = {
        table: TableType<any>;
        meta: ApiMeta;
    };

    let { table, meta }: PaginationProps = $props();
</script>

<div class="flex items-center justify-between px-4 py-4 bg-white shadow-sm rounded-lg">
    <div class="flex-1 text-xs uppercase tracking-widest font-bold">
        {#if meta?.total > 0}
            {meta?.from} to {meta?.to} of {meta?.total} rows.
        {/if}
    </div>
    <div class="flex items-center space-x-6 lg:space-x-8">
        <div class="flex items-center space-x-2">
            <p class="text-xs font-bold uppercase tracking-widest">Rows per page</p>
            <Select.Root
                allowDeselect={false}
                type="single"
                value={`${table.getState().pagination.pageSize}`}
                onValueChange={(value) => {
                    table.setPagination({
                        pageIndex: 0,
                        pageSize: Number(value)
                    });
                }}
            >
                <Select.Trigger class="h-8 w-[70px]">
                    {String(table.getState().pagination.pageSize)}
                </Select.Trigger>
                <Select.Content side="bottom">
                    {#each [10, 20, 30, 40, 50] as pageSize (pageSize)}
                        <Select.Item value={`${pageSize}`}>
                            {pageSize}
                        </Select.Item>
                    {/each}
                </Select.Content>
            </Select.Root>
        </div>
        <div class="flex w-[100px] items-center justify-center text-xs font-bold uppercase tracking-widest">
            Page {table.getState().pagination.pageIndex + 1} of
            {meta?.last_page}
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