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
</script>

{#each data as row, index}
	<DataTableRow
		{row}
		{columns}
		{index}
		{selectable}
		selected={selectedRows.has(row.id || index)}
		onSelect={(selected) => onRowSelect(row.id || index, selected)}
		className={rowClassName}
		{cellClassName}
	/>
{/each}
