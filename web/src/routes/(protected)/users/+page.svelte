
<script lang="ts">
	import { DataTable } from '$components/data-table/index';
	import type { User } from '$models/user';
	import type { 
		DataTableConfig, 
		PaginationConfig, 
		FilterConfig, 
		SortConfig 
	} from '$components/data-table/types';
	import { Button } from '$ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$ui/card';
	import { Separator } from '$ui/separator';
	import { onMount } from 'svelte';
	import { columns as columnDefinitions } from './columns';
    import type { ApiMeta } from '$resources/api';

    let { data } = $props();
    let users = $derived(data.usersCollection.data.map((user) => user.attributes));
    let usersMeta = $derived(data.usersCollection.meta as ApiMeta);
	
	// Data table configuration
	const config: DataTableConfig<User> = {
		model: {} as User,
		columns: columnDefinitions,
		apiEndpoint: '/api/users', // This would be your actual API endpoint
		paginationSiblingCount: { desktop: 3, mobile: 1 },
		sortable: true,
		filterable: true,
		selectable: true,
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