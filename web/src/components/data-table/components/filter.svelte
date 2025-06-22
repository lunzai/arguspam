<script lang="ts" generics="T">
	import type { ColumnDefinition, FilterConfig } from '../types';
	import { Input } from '$ui/input';
	import { Button } from '$ui/button';
	import { createEventDispatcher } from 'svelte';

	interface Props<T> {
		columns: ColumnDefinition<T>[];
		filters: FilterConfig;
		class?: string;
	}

	let { columns, filters, class: className = '' }: Props<T> = $props();

	const dispatch = createEventDispatcher<{
		filter: FilterConfig;
	}>();

	const filterableColumns = $derived(columns.filter(col => col.filterable));

	function handleFilterChange(columnKey: string, value: string) {
		const newFilters = { ...filters };
		
		if (value.trim()) {
			newFilters[columnKey] = {
				value: value.trim(),
				operator: 'contains'
			};
		} else {
			delete newFilters[columnKey];
		}
		
		dispatch('filter', newFilters);
	}

	function clearFilters() {
		dispatch('filter', {});
	}
</script>

{#if filterableColumns.length > 0}
	<div class="flex flex-wrap gap-4 items-end p-4 border-b {className}">
		{#each filterableColumns as column}
			<div class="flex flex-col gap-2 min-w-[200px]">
				<label for={`filter-${column.key}`} class="text-sm font-medium">
					{column.title}
				</label>
				<Input
					id={`filter-${column.key}`}
					type="text"
					placeholder={`Filter ${column.title.toLowerCase()}...`}
					value={filters[column.key]?.value || ''}
					on:input={(e) => handleFilterChange(column.key, e.currentTarget.value)}
				/>
			</div>
		{/each}
		
		<Button 
			variant="outline" 
			size="sm" 
			on:click={clearFilters}
			disabled={Object.keys(filters).length === 0}
		>
			Clear Filters
		</Button>
	</div>
{/if}
