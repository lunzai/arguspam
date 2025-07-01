<script lang="ts">
	import { TableRow } from '$ui/table';
	import type { ColumnDefinition } from '../types';
	import DataTableCell from './cell.svelte';
	import CellBadge from './cell-badge.svelte';
	import CellAction from './cell-action.svelte';
	import CellHoverCard from './cell-hover-card.svelte';

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
				{@const renderedContent = column.renderer(row[column.key], row, index)}
				{#if typeof renderedContent === 'string'}
					{@html renderedContent}
				{:else}
					{renderedContent}
				{/if}
			{:else if column.type === 'badge'}
				<CellBadge {...column.componentProps?.(row[column.key], row, index) || {}} />
			{:else if column.type === 'icon'}
				Icon
			{:else if column.type === 'hover-card'}
				<CellHoverCard {...column.componentProps?.(row[column.key], row, index) || {}} />
			{:else if column.type === 'text'}
				{row[column.key] || column.emptyText || ''}
			{:else if column.type === 'boolean'}
				{row[column.key] ? column.booleanTrue || 'Yes' : column.booleanFalse || 'No'}
			{:else if column.type === 'actions'}
				<CellAction {...column.componentProps?.(row[column.key], row, index) || {}} />
			{:else}
				{row[column.key] || ''}
			{/if}
		</DataTableCell>
	{/each}
</TableRow>
