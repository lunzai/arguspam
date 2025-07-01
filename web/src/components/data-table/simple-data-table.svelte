<script lang="ts" generics="TData, TValue">
	import {
		type ColumnDef,
		type PaginationState,
		type SortingState,
		type ColumnFiltersState,
		getCoreRowModel,
		getPaginationRowModel,
		getSortedRowModel,
		getFilteredRowModel
	} from '@tanstack/table-core';
	import { rankItem } from '@tanstack/match-sorter-utils';
	import type { FilterFn } from '@tanstack/table-core';
	import { createSvelteTable, FlexRender } from '$ui/data-table';
	import * as Table from '$ui/table';
	import { Button } from '$ui/button';
	import { ChevronUp, ChevronDown } from '@lucide/svelte';
	import { Input } from '$ui/input';

	type DataTableProps<TData, TValue> = {
		columns: ColumnDef<TData, TValue>[];
		data: TData[];
	};

	let { data, columns }: DataTableProps<TData, TValue> = $props();
	let pagination = $state<PaginationState>({ pageIndex: 0, pageSize: 20 });
	let sorting = $state<SortingState>([]);
	let columnFilters = $state<ColumnFiltersState>([]);
	let globalFilter = $state<string>('');

	const fuzzyFilter: FilterFn<any> = (row, columnId, value, addMeta) => {
		const itemRank = rankItem(row.getValue(columnId), value);
		addMeta({ itemRank });
		return itemRank.passed;
	};

	const table = createSvelteTable({
		get data() {
			return data;
		},
		columns,
		onSortingChange: (updater) => {
			if (typeof updater === 'function') {
				sorting = updater(sorting);
			} else {
				sorting = updater;
			}
		},
		onPaginationChange: (updater) => {
			if (typeof updater === 'function') {
				pagination = updater(pagination);
			} else {
				pagination = updater;
			}
		},
		onColumnFiltersChange: (updater) => {
			if (typeof updater === 'function') {
				columnFilters = updater(columnFilters);
			} else {
				columnFilters = updater;
			}
		},
		onGlobalFilterChange: (updater) => {
			if (typeof updater === 'function') {
				globalFilter = updater(globalFilter);
			} else {
				globalFilter = updater;
			}
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
			get globalFilter() {
				return globalFilter;
			}
		},
		globalFilterFn: fuzzyFilter,
		getCoreRowModel: getCoreRowModel(),
		getPaginationRowModel: getPaginationRowModel(),
		getSortedRowModel: getSortedRowModel(),
		getFilteredRowModel: getFilteredRowModel()
	});

	const handleKeyUp = (e: any) => {
		table.setGlobalFilter(String(e?.target?.value));
	};

	let resultFrom = $derived(pagination.pageIndex * pagination.pageSize + 1);
	let resultTo = $derived(pagination.pageIndex * pagination.pageSize + pagination.pageSize);
</script>

<div class="flex flex-col gap-3">
	<Input placeholder="Search..." onkeyup={handleKeyUp} bind:value={globalFilter} />

	{#if table.getPageCount() > 1}
		<div>
			Showing {resultFrom} to {resultTo > table.getRowCount() ? table.getRowCount() : resultTo} of {table.getRowCount()}
			results
		</div>
	{/if}

	<div class="rounded-md border">
		<Table.Root>
			<Table.Header>
				{#each table.getHeaderGroups() as headerGroup (headerGroup.id)}
					<Table.Row>
						{#each headerGroup.headers as header (header.id)}
							<Table.Head colspan={header.colSpan}>
								{#if !header.isPlaceholder}
									<!-- svelte-ignore a11y_click_events_have_key_events -->
									<!-- svelte-ignore a11y_no_static_element_interactions -->
									<div
										class:cursor-pointer={header.column.getCanSort()}
										class:select-none={!header.column.getCanSort()}
										onclick={header.column.getToggleSortingHandler()}
										class="flex items-center gap-2"
									>
										<FlexRender
											content={header.column.columnDef.header}
											context={header.getContext()}
										/>
										{#if header.column.getIsSorted().toString() === 'asc'}
											<ChevronUp class="h-4 w-4" />
										{:else if header.column.getIsSorted().toString() === 'desc'}
											<ChevronDown class="h-4 w-4" />
										{/if}
									</div>
								{/if}
							</Table.Head>
						{/each}
					</Table.Row>
				{/each}
			</Table.Header>
			<Table.Body>
				{#each table.getRowModel().rows as row (row.id)}
					<Table.Row data-state={row.getIsSelected() && 'selected'}>
						{#each row.getVisibleCells() as cell (cell.id)}
							<Table.Cell>
								<FlexRender content={cell.column.columnDef.cell} context={cell.getContext()} />
							</Table.Cell>
						{/each}
					</Table.Row>
				{:else}
					<Table.Row>
						<Table.Cell colspan={columns.length} class="h-24 text-center">No results.</Table.Cell>
					</Table.Row>
				{/each}
			</Table.Body>
		</Table.Root>
	</div>

	{#if table.getPageCount() > 1}
		<div class="flex items-center justify-end space-x-2">
			<Button
				variant="outline"
				size="sm"
				onclick={() => table.previousPage()}
				disabled={!table.getCanPreviousPage()}
			>
				Previous
			</Button>
			<Button
				variant="outline"
				size="sm"
				onclick={() => table.nextPage()}
				disabled={!table.getCanNextPage()}
			>
				Next
			</Button>
		</div>
	{/if}
</div>
