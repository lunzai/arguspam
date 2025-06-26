
<script lang="ts">
	import { DataTable } from '$components/data-table/index';
	import type { User } from '$models/user';
	import type { 
		DataTableConfig, 
		PaginationConfig, 
		FilterConfig, 
		SortConfig 
	} from '$components/data-table/types';
	import { onMount } from 'svelte';
    import type { ApiMeta } from '$resources/api';
	import { shortDateTime } from '$lib/utils/date';
	import type { ColumnDefinition } from '$components/data-table/types';
	import type { CellBadge } from '$components/data-table/types';

    let { data } = $props();
    let users = $derived(data.usersCollection.data.map((user) => user.attributes));
    let usersMeta = $derived(data.usersCollection.meta as ApiMeta);
	
	export const columns: ColumnDefinition<User>[] = [
		{
			key: 'id',
			title: 'ID',
			sortable: true,
		},
		{
			key: 'name',
			title: 'Name',
			sortable: true,
			filterable: true,
		},
		{
			key: 'email',
			title: 'Email',
			sortable: true,
			filterable: true,
			renderer: (value: string, row: User) => {
				const mailWarningIcon = '<svg class="text-red-500" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-warning-icon lucide-mail-warning"><path d="M22 10.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h12.5"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/><path d="M20 14v4"/><path d="M20 22v.01"/></svg>';
				return `<div class="flex items-center gap-2">${value}` + (!row.email_verified_at ? mailWarningIcon : '') + '</div>';
			}
		},
		{
			key: 'two_factor_enabled',
			title: '2FA',
			sortable: true,
			filterable: true,
			type: 'badge',
			componentProps: (value: string, row: User) => {
				let values: CellBadge[] = [];
				if (row.two_factor_enabled) {
					values.push({
						value:'Enabled',
						variant: 'outline',
					});
					values.push({
						value: row.two_factor_confirmed_at ? 'Enrolled' : 'Not Enrolled',
						variant: row.two_factor_confirmed_at ? 'outline' : 'destructive',
					});
				} else {
					values.push({
						value: 'Not Enabled',
						variant: 'secondary',
					});
				}				
				return { values };
			}
		},
		{
			key: 'last_login_at',
			title: 'Last Login At',
			sortable: true,
			filterable: true,
			visible: true,
			renderer: (value: string) => {
				return value ? shortDateTime(value) : '-';
			}
		},
		{
			key: 'status',
			title: 'Status',
			sortable: true,
			filterable: true,
			type: 'badge',
			componentProps: (value: string, row: User) => {
				let values: CellBadge[] = [
					{
						value: value === 'active' ? 'Active' : 'Inactive',
						variant: value === 'active' ? 'default' : 'secondary',
					},
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
			visible: false,
			renderer: (value: string) => {
				return value ? shortDateTime(value) : '';
			}
		},
	];

	// Data table configuration
	const config: DataTableConfig<User> = {
		model: {} as User,
		columns: columns,
		apiEndpoint: '/api/search/users', // This would be your actual API endpoint
		paginationSiblingCount: { desktop: 3, mobile: 1 },
		sortable: true,
		filterable: true,
		selectable: false,
		loading: false,
		emptyMessage: 'No users found',
		className: 'border rounded-lg',
		headerClassName: 'bg-muted/50',
		bodyClassName: 'divide-y',
		rowClassName: 'hover:bg-muted/50 transition-colors',
		cellClassName: 'p-3',
		headerCellClassName: 'p-3 font-medium'
	};

	// Event handlers
	function handleDataChange(data: User[]) {
		console.log('Data changed:', data);
	}

	function handlePaginationChange(pagination: PaginationConfig) {
		console.log('Pagination changed:', pagination);
	}

	function handleFilterChange(filters: FilterConfig) {
		console.log('Filters changed:', filters);
	}

	function handleSortChange(sort: SortConfig) {
		console.log('Sort changed:', sort);
	}

	function handleRowSelect(selectedRows: Set<string | number>) {
		console.log('Selected rows:', selectedRows);
	}

	// Mock API functions for testing
	function editUser(id: number) {
		console.log('Edit user:', id);
		alert(`Edit user with ID: ${id}`);
	}

	function deleteUser(id: number) {
		console.log('Delete user:', id);
		if (confirm(`Are you sure you want to delete user with ID: ${id}?`)) {
			alert(`User ${id} deleted!`);
		}
	}

	// Make functions globally available for the renderer
	onMount(() => {
		(window as any).editUser = editUser;
		(window as any).deleteUser = deleteUser;
	});
</script>

<h1 class="text-2xl font-medium">Users</h1>
            
<!-- Data Table Component -->
<DataTable
    model={{} as User}
    config={config}
    initialData={users}
    initialPagination={{
        currentPage: usersMeta.current_page,
        from: usersMeta.from,
        to: usersMeta.to,
        perPage: usersMeta.per_page,
        lastPage: usersMeta.last_page,
        total: usersMeta.total
    }}
    onDataChange={handleDataChange}
    onPaginationChange={handlePaginationChange}
    onFilterChange={handleFilterChange}
    onSortChange={handleSortChange}
    onRowSelect={handleRowSelect}
/>