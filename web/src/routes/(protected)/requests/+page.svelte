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
	import type { Asset } from '$models/asset';
	import type { User } from '$models/user';
	import type { CellBadge } from '$components/data-table/types';
	import { capitalizeWords } from '$lib/utils/string';
	import { formatDistanceStrict } from 'date-fns';

	let initialSearchParams = page.url.searchParams;
	const modelName = 'requests';

	export const columns: ColumnDefinition<Request>[] = [
		{
			key: 'id',
			title: 'ID',
			sortable: true
		},
		{
			key: 'asset_id',
			title: 'Asset',
			sortable: true,
			filterable: true,
			renderer: (value: string, row: Request, relationships) => {
				const asset = relationships.asset?.attributes as Asset;
				return `<div>${asset?.name}</div>
                <div class="text-muted-foreground text-xs truncate">${asset?.host}:${asset?.port}</div>
                `;
			}
		},
		{
			key: 'start_datetime',
			title: 'Start/End',
			sortable: true,
			filterable: true,
			renderer: (value: string, row: Request) => {
				const startDatetime = row.start_datetime;
				const endDatetime = row.end_datetime;
				return `<div>${shortDateTime(startDatetime)} -</div>
                <div>${shortDateTime(endDatetime)}</div>
                <div class="text-muted-foreground text-xs">${formatDistanceStrict(startDatetime, endDatetime)}</div>
                `;
			}
		},
		{
			key: 'reason',
			title: 'Reason',
			sortable: true,
			filterable: true,
			renderer: (value: string, row: Request, relationships) => {
				return `<div class="max-w-80 wrap-break-word whitespace-break-spaces">${value}</div>`;
			}
		},
		{
			key: 'status',
			title: 'Status',
			sortable: true,
			filterable: true,
			type: 'badge',
			componentProps: (value: string, row: Request) => {
				//  "default" | "secondary" | "destructive" | "outline"
				const wrapperClassName = 'text-sm';
				let variant = 'default';
				let className = '';
				switch (value) {
					case 'pending':
						variant = 'default';
						break;
					case 'submitted':
						variant = 'secondary';
						className = 'bg-blue-500 text-white';
						break;
					case 'approved':
						variant = 'secondary';
						className = 'bg-green-500 text-white';
						break;
					case 'rejected':
						variant = 'destructive';
						break;
					case 'expired':
						variant = 'outline';
						break;
				}
				let values: CellBadge[] = [
					{
						value: capitalizeWords(value),
						variant,
						className
					}
				];
				return { values, className: wrapperClassName };
			}
		},
		{
			key: 'requester_id',
			title: 'Requester',
			sortable: false,
			filterable: false,
			renderer: (value: string, row: Request, relationships) => {
				const user = relationships.requester?.attributes as User;
				return `
                <div>${user?.name}</div>
                <div class="text-muted-foreground text-xs truncate">${user?.email}</div>
                <div class="text-muted-foreground text-xs truncate">${row.created_at ? shortDateTime(row.created_at) : '-'}</div>
                `;
			}
		},
		{
			key: 'approver_id',
			title: 'Approver',
			sortable: false,
			filterable: false,
			renderer: (value: string, row: Request, relationships) => {
				let user: User | null = null;
				let datetime: Date | null = null;
				if (row.approved_at) {
					user = relationships.approver?.attributes as User;
					datetime = row.approved_at;
				}
				if (row.rejected_at) {
					user = relationships.rejecter?.attributes as User;
					datetime = row.rejected_at;
				}
				if (!user) {
					return '-';
				}
				return `
                <div>${user?.name || '-'}</div>
                <div class="text-muted-foreground text-xs truncate">${user?.email || '-'}</div>
                <div class="text-muted-foreground text-xs truncate">${datetime ? shortDateTime(datetime) : '-'}</div>
                `;
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
						}
						// {
						// 	label: 'Edit',
						// 	icon: Pencil,
						// 	href: `/${modelName}/${row.id}/edit`,
						// 	variant: 'link',
						// 	class: 'hover:text-blue-500'
						// }
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
	function handleDataChange(data: Request[]) {}

	function handlePaginationChange(pagination: PaginationConfig) {}

	function handleFilterChange(filters: FilterConfig) {}

	function handleSortChange(sort: SortConfig) {}

	function handleRowSelect(selectedRows: Set<string | number>) {}
</script>

<h1 class="text-2xl font-medium capitalize">{modelName}</h1>

<!-- Data Table Component -->
<DataTable
	model={{} as Request}
	{config}
	{initialSearchParams}
	initialInclude={['asset', 'requester', 'approver', 'rejecter']}
	initialSort={{ column: 'created_at', direction: 'desc' }}
	onDataChange={handleDataChange}
	onPaginationChange={handlePaginationChange}
	onFilterChange={handleFilterChange}
	onSortChange={handleSortChange}
	onRowSelect={handleRowSelect}
/>
