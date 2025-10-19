<script lang="ts">
	import { DataTable } from '$components/data-table/index';
	import type { Session } from '$models/session';
	import type {
		DataTableConfig,
		PaginationConfig,
		FilterConfig,
		SortConfig
	} from '$components/data-table/types';
	import { shortDateTime, relativeDateTime } from '$lib/utils/date';
	import type { ColumnDefinition } from '$components/data-table/types';
	import { page } from '$app/state';
	import { Pencil, NotebookText } from '@lucide/svelte';
	import type { Asset } from '$models/asset';
	import { formatDistanceStrict } from 'date-fns';
	import type { CellBadge } from '$components/data-table/types';
	import { capitalizeWords } from '$lib/utils/string';
	import type { User } from '$models/user';

	let initialSearchParams = page.url.searchParams;
	const modelName = 'sessions';

	export const columns: ColumnDefinition<Session>[] = [
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
			renderer: (value: string, row: Session, relationships) => {
				const asset = relationships.asset?.attributes as Asset;
				return `<div>${asset?.name}</div>
                <div class="text-muted-foreground text-xs truncate">${asset?.host}:${asset?.port}</div>
                `;
			}
		},
		{
			key: 'scheduled_start_datetime',
			title: 'Start/End',
			sortable: true,
			filterable: true,
			renderer: (value: string, row: Session) => {
				const startDatetime = row.scheduled_start_datetime;
				const endDatetime = row.scheduled_end_datetime;
				return `<div>${shortDateTime(startDatetime)} -</div>
                <div>${shortDateTime(endDatetime)}</div>
                <div class="text-muted-foreground text-xs">${formatDistanceStrict(startDatetime, endDatetime)}</div>
                `;
			}
		},
		{
			key: 'status',
			title: 'Status',
			sortable: true,
			filterable: true,
			type: 'badge',
			componentProps: (value: string, row: Session) => {
				const wrapperClassName = 'text-sm';
				let variant = 'default';
				let className = '';
				switch (value) {
					case 'pending':
						variant = 'default';
						break;
					case 'started':
						variant = 'secondary';
						className = 'bg-green-500 text-white';
						break;
					case 'ended':
						variant = 'secondary';
						className = 'bg-blue-500 text-white';
						break;
					case 'terminated':
						variant = 'destructive';
						break;
					case 'cancelled':
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
			renderer: (value: string, row: Session, relationships) => {
				const user = relationships.requester?.attributes as User;
				return `
                <div>${user?.name}</div>
                <div class="text-muted-foreground text-xs truncate">${user?.email}</div>
                `;
			}
		},
		{
			key: 'approver_id',
			title: 'Approver',
			sortable: false,
			filterable: false,
			renderer: (value: string, row: Session, relationships) => {
				const user = relationships.approver?.attributes as User;
				return `
                <div>${user?.name || '-'}</div>
                <div class="text-muted-foreground text-xs truncate">${user?.email || '-'}</div>
                `;
			}
		},
		{
			key: 'actions',
			title: 'Actions',
			type: 'actions',
			sortable: false,
			componentProps: (value: string, row: Session) => {
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
	const config: DataTableConfig<Session> = {
		model: {} as Session,
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
	function handleDataChange(data: Session[]) {}

	function handlePaginationChange(pagination: PaginationConfig) {}

	function handleFilterChange(filters: FilterConfig) {}

	function handleSortChange(sort: SortConfig) {}

	function handleRowSelect(selectedRows: Set<string | number>) {}
</script>

<h1 class="text-2xl font-medium capitalize">{modelName}</h1>

<!-- Data Table Component -->
<DataTable
	model={{} as Session}
	{config}
	{initialSearchParams}
	initialInclude={['asset', 'requester', 'approver', 'request']}
	initialSort={{ column: 'created_at', direction: 'desc' }}
	onDataChange={handleDataChange}
	onPaginationChange={handlePaginationChange}
	onFilterChange={handleFilterChange}
	onSortChange={handleSortChange}
	onRowSelect={handleRowSelect}
/>
