<script lang="ts">
	import type { ColumnDefinition } from '../types';
	import DataTableRow from './row.svelte';

	interface Props {
		data: any[];
		columns: ColumnDefinition[];
		selectable?: boolean;
		selectedRows: Set<string | number>;
		onRowSelect: (rowId: string | number, selected: boolean) => void;
		className?: string;
		rowClassName?: string;
		cellClassName?: string;
	}

	let {
		data,
		columns,
		selectable = false,
		selectedRows,
		onRowSelect,
		className = '',
		rowClassName = '',
		cellClassName = ''
	}: Props = $props();

	// Create a reactive function to check if a row is selected
	function isRowSelected(rowId: string | number) {
		return selectedRows.has(rowId);
	}
</script>

{#each data as row, index}
	{@const rowId = row.id || index}
	<DataTableRow
		{row}
		{columns}
		{index}
		{selectable}
		selected={isRowSelected(rowId)}
		onSelect={(selected) => onRowSelect(rowId, selected)}
		className={rowClassName}
		{cellClassName}
	/>
{/each}
