<script lang="ts">
	import { DataTable } from '$components/data-table/index';
	import type { Role } from '$models/role';
	import type {
		DataTableConfig,
		PaginationConfig,
		FilterConfig,
		SortConfig
	} from '$components/data-table/types';
	import { shortDateTime } from '$lib/utils/date';
	import type { ColumnDefinition } from '$components/data-table/types';
	import { page } from '$app/state';
	import { NotebookText, PlusIcon } from '@lucide/svelte';
	import type { CellBadge } from '$components/data-table/types';
	import { Button } from '$ui/button';
	import FormDialog from './form-dialog.svelte';
	import { goto } from '$app/navigation';

	let { data }: { data: any } = $props();
	let initialSearchParams = page.url.searchParams;
	const modelName = 'roles';
	let addRoleDialogIsOpen = $state(false);

	export const columns: ColumnDefinition<Role>[] = [
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
			componentProps: (value: string, row: Role) => {
				return {
					triggerLabel: value,
					hoverContent: row.description
				};
			}
		},
		{
			key: 'is_default',
			title: 'Default Role',
			sortable: true,
			filterable: true,
			type: 'badge',
			componentProps: (value: string, row: Role) => {
				let values: CellBadge[] = [
					{
						value: value ? 'Yes' : 'No',
						variant: value ? 'default' : 'secondary'
					}
				];
				return { values };
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
			componentProps: (value: string, row: Role) => {
				return {
					actions: [
						{
							label: 'View',
							icon: NotebookText,
							href: `/users/${modelName}/${row.id}`,
							variant: 'link',
							class: 'hover:text-blue-500'
						}
					]
				};
			}
		}
	];

	// Data table configuration
	const config: DataTableConfig<Role> = {
		model: {} as Role,
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
	function handleDataChange(data: Role[]) {}

	function handlePaginationChange(pagination: PaginationConfig) {}

	function handleFilterChange(filters: FilterConfig) {}

	function handleSortChange(sort: SortConfig) {}

	function handleRowSelect(selectedRows: Set<string | number>) {}
</script>

<div class="flex items-center justify-between">
	<h1 class="text-2xl font-medium capitalize">{modelName}</h1>
	<Button
		variant="outline"
		class="gap-2 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500"
		onclick={() => {
			addRoleDialogIsOpen = true;
		}}
	>
		<PlusIcon class="h-4 w-4" />
		<span>Add Role</span>
	</Button>
</div>

<FormDialog
	bind:isOpen={addRoleDialogIsOpen}
	model={data.model}
	data={data.form}
	onSuccess={async (data: Role) => {
		await goto(`/users/${modelName}/${data.id}`);
		addRoleDialogIsOpen = false;
	}}
/>

<!-- Data Table Component -->
<DataTable
	model={{} as Role}
	{config}
	{initialSearchParams}
	onDataChange={handleDataChange}
	onPaginationChange={handlePaginationChange}
	onFilterChange={handleFilterChange}
	onSortChange={handleSortChange}
	onRowSelect={handleRowSelect}
/>
