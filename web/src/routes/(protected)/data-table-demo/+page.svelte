
<script lang="ts">
	import { DataTable } from '$components/data-table/index';
	import type { User } from '$models/user';
	import type { 
		DataTableConfig, 
		ColumnDefinition, 
		PaginationConfig, 
		FilterConfig, 
		SortConfig 
	} from '$components/data-table/types';
	import { Badge } from '$ui/badge';
	import { Button } from '$ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$ui/card';
	import { Separator } from '$ui/separator';
	import { onMount } from 'svelte';

	// Mock data for testing
	const mockUsers: User[] = [
		{
			id: 1,
			name: 'John Doe',
			email: 'john.doe@example.com',
			email_verified_at: '2024-01-15T10:30:00Z',
			two_factor_enabled: true,
			two_factor_confirmed_at: '2024-01-20T14:20:00Z',
			status: 'active',
			last_login_at: '2024-12-01T09:15:00Z',
			created_by: null,
			updated_by: null,
			created_at: '2024-01-01T00:00:00Z',
			updated_at: '2024-12-01T09:15:00Z'
		},
		{
			id: 2,
			name: 'Jane Smith',
			email: 'jane.smith@example.com',
			email_verified_at: null,
			two_factor_enabled: false,
			two_factor_confirmed_at: null,
			status: 'inactive',
			last_login_at: '2024-11-28T16:45:00Z',
			created_by: 1,
			updated_by: 1,
			created_at: '2024-01-15T00:00:00Z',
			updated_at: '2024-11-28T16:45:00Z'
		},
		{
			id: 3,
			name: 'Bob Johnson',
			email: 'bob.johnson@example.com',
			email_verified_at: '2024-02-01T11:00:00Z',
			two_factor_enabled: true,
			two_factor_confirmed_at: null,
			status: 'active',
			last_login_at: '2024-12-01T08:30:00Z',
			created_by: 1,
			updated_by: 1,
			created_at: '2024-02-01T00:00:00Z',
			updated_at: '2024-12-01T08:30:00Z'
		},
		{
			id: 4,
			name: 'Alice Brown',
			email: 'alice.brown@example.com',
			email_verified_at: '2024-03-10T13:20:00Z',
			two_factor_enabled: false,
			two_factor_confirmed_at: null,
			status: 'active',
			last_login_at: '2024-11-30T10:15:00Z',
			created_by: 1,
			updated_by: 1,
			created_at: '2024-03-10T00:00:00Z',
			updated_at: '2024-11-30T10:15:00Z'
		},
		{
			id: 5,
			name: 'Charlie Wilson',
			email: 'charlie.wilson@example.com',
			email_verified_at: null,
			two_factor_enabled: true,
			two_factor_confirmed_at: '2024-04-05T15:45:00Z',
			status: 'inactive',
			last_login_at: '2024-11-25T12:00:00Z',
			created_by: 1,
			updated_by: 1,
			created_at: '2024-04-01T00:00:00Z',
			updated_at: '2024-11-25T12:00:00Z'
		}
	];

	// Column definitions
	const columns: ColumnDefinition<User>[] = [
		{
			key: 'id',
			title: 'ID',
			sortable: true,
			align: 'left'
		},
		{
			key: 'name',
			title: 'Name',
			sortable: true,
			filterable: true,
			width: '200px'
		},
		{
			key: 'email',
			title: 'Email',
			sortable: true,
			filterable: true,
		},
		{
			key: 'status',
			title: 'Status',
			sortable: true,
			filterable: true,
			align: 'center',
			renderer: (value: string) => {
				const variant = value === 'active' ? 'default' : 'secondary';
				return `<span class="badge badge-${variant}">${value}</span>`;
			}
		},
		{
			key: 'email_verified_at',
			title: 'Email Verified',
			sortable: true,
			align: 'center',
			renderer: (value: string | null) => {
				return value ? '✅' : '❌';
			}
		},
		{
			key: 'two_factor_enabled',
			title: '2FA',
			sortable: true,
			align: 'center',
			renderer: (value: boolean) => {
				return value ? '🔐' : '🔓';
			}
		},
		{
			key: 'last_login_at',
			title: 'Last Login',
			sortable: true,
			renderer: (value: string | null) => {
				if (!value) return 'Never';
				return new Date(value).toLocaleDateString('en-US', {
					year: 'numeric',
					month: 'short',
					day: 'numeric',
					hour: '2-digit',
					minute: '2-digit'
				});
			}
		},
		{
			key: 'created_at',
			title: 'Created',
			sortable: true,
			width: '120px',
			renderer: (value: string) => {
				return new Date(value).toLocaleDateString('en-US', {
					year: 'numeric',
					month: 'short',
					day: 'numeric'
				});
			}
		},
		{
			key: 'actions',
			title: 'Actions',
			align: 'center',
			sortable: false,
			renderer: (value: any, row: User) => {
				return `
					<div class="flex gap-2">
						<button class="btn btn-sm btn-outline" onclick="editUser(${row.id})">Edit</button>
						<button class="btn btn-sm btn-destructive" onclick="deleteUser(${row.id})">Delete</button>
					</div>
				`;
			}
		}
	];

	// Data table configuration
	const config: DataTableConfig<User> = {
		model: {} as User,
		columns,
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

<svelte:head>
	<title>Data Table Demo</title>
</svelte:head>

<div class="container mx-auto p-6 space-y-6">
	<Card>
		<CardHeader>
			<CardTitle>Data Table Component Demo</CardTitle>
			<CardDescription>
				Testing the customizable data table component with User model
			</CardDescription>
		</CardHeader>
		<CardContent>
			<div class="space-y-4">
				<div class="flex items-center justify-between">
					<div>
						<h3 class="text-lg font-semibold">Users Table</h3>
						<p class="text-sm text-muted-foreground">
							Features: Sorting, Filtering, Pagination, Row Selection, Custom Renderers
						</p>
					</div>
					<Button variant="outline" size="sm">
						Add New User
					</Button>
				</div>
				
				<Separator />
				
				<!-- Data Table Component -->
				<DataTable
					model={{} as User}
					config={config}
					initialData={mockUsers}
					initialPagination={{
						currentPage: 1,
						from: 1,
						to: mockUsers.length,
						perPage: 10,
						lastPage: 1,
						total: mockUsers.length
					}}
					onDataChange={handleDataChange}
					onPaginationChange={handlePaginationChange}
					onFilterChange={handleFilterChange}
					onSortChange={handleSortChange}
					onRowSelect={handleRowSelect}
				/>
			</div>
		</CardContent>
	</Card>

	<!-- Feature Documentation -->
	<Card>
		<CardHeader>
			<CardTitle>Component Features</CardTitle>
		</CardHeader>
		<CardContent>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<div class="space-y-2">
					<h4 class="font-semibold">Core Features</h4>
					<ul class="text-sm space-y-1 text-muted-foreground">
						<li>• Server-side pagination</li>
						<li>• Column sorting (asc/desc)</li>
						<li>• Row selection (single/multiple)</li>
						<li>• Custom column renderers</li>
						<li>• Responsive design</li>
					</ul>
				</div>
				<div class="space-y-2">
					<h4 class="font-semibold">Customization</h4>
					<ul class="text-sm space-y-1 text-muted-foreground">
						<li>• Custom CSS classes</li>
						<li>• Column alignment</li>
						<li>• Column visibility</li>
						<li>• Custom cell content</li>
						<li>• Event callbacks</li>
					</ul>
				</div>
			</div>
		</CardContent>
	</Card>

	<!-- Usage Example -->
	<Card>
		<CardHeader>
			<CardTitle>Usage Example</CardTitle>
		</CardHeader>
		<CardContent>
			<pre class="bg-muted p-4 rounded-lg text-sm overflow-x-auto"><code>{`// Column definition example
const columns: ColumnDefinition<User>[] = [
  {
    key: 'name',
    title: 'Name',
    sortable: true,
    filterable: true,
    renderer: (value, row) => \`<strong>\${value}</strong>\`
  }
];

// Configuration
const config: DataTableConfig<User> = {
  model: {} as User,
  columns,
  apiEndpoint: '/api/users',
  sortable: true,
  filterable: true,
  selectable: true
};

// Usage
<DataTable
  model={{} as User}
  config={config}
  onDataChange={handleDataChange}
  onSortChange={handleSortChange}
/>`}</code></pre>
		</CardContent>
	</Card>
</div>
