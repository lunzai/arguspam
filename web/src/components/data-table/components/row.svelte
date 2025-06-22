<script lang="ts">
	import { TableRow } from '$ui/table';
	import type { ColumnDefinition } from '../types';
	import DataTableCell from './cell.svelte';

	interface Props {
		row: any;
		columns: ColumnDefinition[];
		index: number;
		selectable?: boolean;
		selected?: boolean;
		onSelect: (selected: boolean) => void;
		className?: string;
		cellClassName?: string;
	}

	let {
		row,
		columns,
		index,
		selectable = false,
		selected = false,
		onSelect,
		className = '',
		cellClassName = ''
	}: Props = $props();
</script>

<TableRow class="hover:bg-gray-50 {className}">
	{#if selectable}
		<DataTableCell className="w-12 {cellClassName}" align="center">
			<input
				type="checkbox"
				checked={selected}
				onchange={(e) => onSelect(e.currentTarget.checked)}
				class="text-primary focus:ring-primary h-4 w-4 rounded border-gray-300"
			/>
		</DataTableCell>
	{/if}

	{#each columns as column}
		<DataTableCell className={cellClassName} align={column.align}>
			{#if column.renderer}
				{@html column.renderer(row[column.key], row, index)}
			{:else}
				{row[column.key] || ''}
			{/if}
		</DataTableCell>
	{/each}
</TableRow>
