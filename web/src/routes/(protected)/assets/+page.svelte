<script lang="ts">
	import { DataTable } from '$components/data-table/index';
	import type { Asset } from '$models/asset';
	import type {
		DataTableConfig,
		PaginationConfig,
		FilterConfig,
		SortConfig
	} from '$components/data-table/types';
	import { shortDateTime } from '$lib/utils/date';
	import type { ColumnDefinition } from '$components/data-table/types';
	import { page } from '$app/state';
	import { Pencil, NotebookText } from '@lucide/svelte';
	import type { CellBadge } from '$components/data-table/types';

	let initialSearchParams = page.url.searchParams;
	const modelName = 'assets';

	export const columns: ColumnDefinition<Asset>[] = [
		{
			key: 'id',
			title: 'ID',
			sortable: true
		},
		{
			key: 'name',
			title: 'Name',
			sortable: true,
			filterable: true,
			type: 'hover-card',
			componentProps: (value: string, row: Asset) => {
				return {
					triggerLabel: value,
					hoverContent: row.description
				};
			}
		},
		{
			key: 'status',
			title: 'Status',
			sortable: true,
			filterable: true,
			type: 'badge',
			componentProps: (value: string, row: Asset) => {
				let values: CellBadge[] = [
					{
						value: value === 'active' ? 'Active' : 'Inactive',
						variant: value === 'active' ? 'default' : 'secondary'
					}
				];
				return { values };
			}
		},
		{
			key: 'dbms',
			title: 'DBMS',
			sortable: true,
			filterable: true,
			renderer: (value: string) => {
				return value ? value.toUpperCase() : '-';
			}
		},
		{
			key: 'created_at',
			title: 'Created At',
			sortable: true,
			filterable: false,
			visible: true,
			renderer: (value: string) => {
				return value ? shortDateTime(value) : '-';
			}
		},
		{
			key: 'updated_at',
			title: 'Updated At',
			sortable: true,
			filterable: false,
			visible: true,
			renderer: (value: string) => {
				return value ? shortDateTime(value) : '';
			}
		},
		{
			key: 'actions',
			title: 'Actions',
			type: 'actions',
			sortable: false,
			componentProps: (value: string, row: Asset) => {
				return {
					actions: [
						{
							label: 'View',
							icon: NotebookText,
							href: `/${modelName}/${row.id}`,
							variant: 'link',
							class: 'hover:text-blue-500'
						},
						{
							label: 'Edit',
							icon: Pencil,
							href: `/${modelName}/${row.id}/edit`,
							variant: 'link',
							class: 'hover:text-blue-500'
						}
					]
				};
			}
		}
	];

	// Data table configuration
	const config: DataTableConfig<Asset> = {
		model: {} as Asset,
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
	function handleDataChange(data: Asset[]) {
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
	model={{} as Asset}
	{config}
	{initialSearchParams}
	onDataChange={handleDataChange}
	onPaginationChange={handlePaginationChange}
	onFilterChange={handleFilterChange}
	onSortChange={handleSortChange}
	onRowSelect={handleRowSelect}
/>
