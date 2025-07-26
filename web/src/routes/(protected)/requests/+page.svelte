<script lang="ts">
	import { DataTable } from '$components/data-table/index';
	import type { Request } from '$models/request';
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

	let initialSearchParams = page.url.searchParams;
	const modelName = 'requests';

	export const columns: ColumnDefinition<Request>[] = [
		{
			key: 'id',
			title: 'ID',
			sortable: true
		},
		// {
		// 	key: 'name',
		// 	title: 'Name',
		// 	sortable: true,
		// 	filterable: true
		// },
		// {
		// 	key: 'email',
		// 	title: 'Email',
		// 	sortable: true,
		// 	filterable: true,
		// 	renderer: (value: string, row: Request) => {
		// 		const mailWarningIcon =
		// 			'<svg class="text-red-500" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-warning-icon lucide-mail-warning"><path d="M22 10.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h12.5"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/><path d="M20 14v4"/><path d="M20 22v.01"/></svg>';
		// 		return (
		// 			`<div class="flex items-center gap-2">${value}` +
		// 			(!row.email_verified_at ? mailWarningIcon : '') +
		// 			'</div>'
		// 		);
		// 	}
		// },
		// {
		// 	key: 'two_factor_enabled',
		// 	title: '2FA',
		// 	sortable: true,
		// 	filterable: true,
		// 	type: 'badge',
		// 	componentProps: (value: string, row: Request) => {
		// 		let values: CellBadge[] = [];
		// 		if (row.two_factor_enabled) {
		// 			values.push({
		// 				value: 'Enabled',
		// 				variant: 'outline'
		// 			});
		// 			values.push({
		// 				value: row.two_factor_confirmed_at ? 'Enrolled' : 'Not Enrolled',
		// 				variant: row.two_factor_confirmed_at ? 'outline' : 'destructive'
		// 			});
		// 		} else {
		// 			values.push({
		// 				value: 'Not Enabled',
		// 				variant: 'secondary'
		// 			});
		// 		}
		// 		return { values };
		// 	}
		// },
		// {
		// 	key: 'last_login_at',
		// 	title: 'Last Login At',
		// 	sortable: true,
		// 	filterable: true,
		// 	visible: true,
		// 	renderer: (value: string) => {
		// 		return value ? shortDateTime(value) : '-';
		// 	}
		// },
		// {
		// 	key: 'status',
		// 	title: 'Status',
		// 	sortable: true,
		// 	filterable: true,
		// 	type: 'badge',
		// 	componentProps: (value: string, row: Request) => {
		// 		let values: CellBadge[] = [
		// 			{
		// 				value: value === 'active' ? 'Active' : 'Inactive',
		// 				variant: value === 'active' ? 'default' : 'secondary'
		// 			}
		// 		];
		// 		return { values };
		// 	}
		// },
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
			visible: false,
			renderer: (value: string) => {
				return value ? shortDateTime(value) : '';
			}
		},
		{
			key: 'actions',
			title: 'Actions',
			type: 'actions',
			sortable: false,
			componentProps: (value: string, row: Request) => {
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
	const config: DataTableConfig<Request> = {
		model: {} as Request,
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
	function handleDataChange(data: Request[]) {
	}

	function handlePaginationChange(pagination: PaginationConfig) {
	}

	function handleFilterChange(filters: FilterConfig) {
	}

	function handleSortChange(sort: SortConfig) {
	}

	function handleRowSelect(selectedRows: Set<string | number>) {
	}
</script>

<h1 class="text-2xl font-medium capitalize">{modelName}</h1>

<!-- Data Table Component -->
<DataTable
	model={{} as Request}
	{config}
	{initialSearchParams}
	onDataChange={handleDataChange}
	onPaginationChange={handlePaginationChange}
	onFilterChange={handleFilterChange}
	onSortChange={handleSortChange}
	onRowSelect={handleRowSelect}
/>
