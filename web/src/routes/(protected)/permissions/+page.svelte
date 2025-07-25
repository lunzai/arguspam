<script lang="ts">
	import { DataTable } from '$components/data-table/index';
	import type { Permission } from '$models/permission';
	import type {
		DataTableConfig,
		PaginationConfig,
		FilterConfig,
		SortConfig
	} from '$components/data-table/types';
	import type { ColumnDefinition } from '$components/data-table/types';
	import { page } from '$app/state';
	import { NotebookText } from '@lucide/svelte';

	let initialSearchParams = page.url.searchParams;
	const modelName = 'permissions';

	export const columns: ColumnDefinition<Permission>[] = [
		{
			key: 'id',
			title: 'ID',
			sortable: true
		},
		{
			key: 'name',
			title: 'Name',
			sortable: true,
			filterable: true
		},
		{
			key: 'description',
			title: 'Description',
			sortable: true,
			filterable: true
		},
		{
			key: 'actions',
			title: 'Actions',
			type: 'actions',
			sortable: false,
			componentProps: (value: string, row: Permission) => {
				return {
					actions: [
						{
							label: 'View',
							icon: NotebookText,
							href: `/${modelName}/${row.id}`,
							variant: 'link',
							class: 'hover:text-blue-500'
						}
					]
				};
			}
		}
	];

	// Data table configuration
	const config: DataTableConfig<Permission> = {
		model: {} as Permission,
		columns: columns,
		apiEndpoint: `/api/search/${modelName}`,
		paginationSiblingCount: { desktop: 3, mobile: 1 },
		sortable: true,
		filterable: true,
		selectable: false,
		loading: false,
		emptyMessage: `No ${modelName} found`,
		className: 'border rounded-lg',
		headerClassName: 'bg-muted/50',
		bodyClassName: 'divide-y',
		rowClassName: 'hover:bg-muted/50 transition-colors',
		cellClassName: 'p-3',
		headerCellClassName: 'p-3 font-medium'
	};

	// Event handlers
	function handleDataChange(data: Permission[]) {
		// console.log('Data changed:', data);
	}

	function handlePaginationChange(pagination: PaginationConfig) {
		// console.log('Pagination changed:', pagination);
	}

	function handleFilterChange(filters: FilterConfig) {
		// console.log('Filters changed:', filters);
	}

	function handleSortChange(sort: SortConfig) {
		// console.log('Sort changed:', sort);
	}

	function handleRowSelect(selectedRows: Set<string | number>) {
		// console.log('Selected rows:', selectedRows);
	}
</script>

<h1 class="text-2xl font-medium capitalize">{modelName}</h1>

<!-- Data Table Component -->
<DataTable
	model={{} as Permission}
	{config}
	{initialSearchParams}
	onDataChange={handleDataChange}
	onPaginationChange={handlePaginationChange}
	onFilterChange={handleFilterChange}
	onSortChange={handleSortChange}
	onRowSelect={handleRowSelect}
/>
