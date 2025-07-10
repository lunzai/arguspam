<script lang="ts">
	import { ChevronUpIcon, ChevronDownIcon } from '@lucide/svelte/icons';
	import { TableRow } from '$ui/table';
	import type { ColumnDefinition, SortDirection } from '../types';
	import DataTableHeaderCell from './header-cell.svelte';

	interface Props {
		columns: ColumnDefinition[];
		sort: { column: string | null; direction: SortDirection };
		selectable?: boolean;
		allSelected?: boolean;
		onSort: (column: string) => void;
		onSelectAll: (selected: boolean) => void;
		className?: string;
		headerCellClassName?: string;
	}

	let {
		columns,
		sort,
		selectable = false,
		allSelected = false,
		onSort,
		onSelectAll,
		className = '',
		headerCellClassName = ''
	}: Props = $props();

	function getSortIcon(columnKey: string) {
		if (sort.column !== columnKey) {
			return null;
		}

		return sort.direction === 'asc' ? ChevronUpIcon : ChevronDownIcon;
	}
</script>

<TableRow class={className}>
	{#if selectable}
		<DataTableHeaderCell className="w-12 {headerCellClassName}" align="center">
			<input
				type="checkbox"
				checked={allSelected}
				onchange={(e) => onSelectAll(e.currentTarget.checked)}
				class="text-primary focus:ring-primary h-4 w-4 rounded border-gray-300"
			/>
		</DataTableHeaderCell>
	{/if}

	{#each columns as column (column.key)}
		<DataTableHeaderCell
			className={headerCellClassName}
			align={column.align}
			width={column.width}
			sortable={column.sortable}
			onclick={() => column.sortable && onSort(column.key)}
		>
			<div class="flex items-center gap-2">
				{#if column.headerRenderer}
					{@html column.headerRenderer(column)}
				{:else}
					<span>{column.title}</span>
				{/if}

				{#if column.sortable}
					<div class="flex flex-col">
						{#if getSortIcon(column.key)}
							{@const SortIcon = getSortIcon(column.key)}
							<SortIcon class="h-4 w-4" />
						{:else}
							<div class="h-4 w-4"></div>
						{/if}
					</div>
				{/if}
			</div>
		</DataTableHeaderCell>
	{/each}
</TableRow>
