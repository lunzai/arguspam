<script lang="ts" generics="T extends BaseModel">
	import type {
		DataTableProps,
		DataTableState,
		DataTableConfig,
		PaginationConfig,
		FilterConfig,
		SortConfig,
		SortDirection,
		ApiResponse,
		ApiRequestParams
	} from './types';
	import DataTableBody from './components/body.svelte';
	import DataTableHeader from './components/header.svelte';
	import DataTablePagination from './components/pagination.svelte';
	import DataTableFilter from './components/filter.svelte';
	import DataTableLoading from './components/loading.svelte';
	import DataTableEmpty from './components/empty.svelte';
	import type { BaseModel } from '$models/base-model';
	import { onMount } from 'svelte';
	import * as Table from '$ui/table';
	import ResultSummary from './components/result-summary.svelte';
	import { replaceState } from '$app/navigation';
	import { Skeleton } from '$ui/skeleton';

	interface Props<T extends BaseModel> extends DataTableProps<T> {
		class?: string;
	}

	let {
		config,
		initialData = [],
		initialInclude = [],
		initialPagination = {
			currentPage: 1,
			from: 0,
			to: 0,
			perPage: 20,
			lastPage: 0,
			total: 0
		},
		initialSearchParams = new URLSearchParams(),
		onDataChange,
		onPaginationChange,
		onFilterChange,
		onSortChange,
		onRowSelect,
		class: className = ''
	}: Props<T> = $props();

	// Derive initial filters and sort from search params (one-time computation)
	const initialFilters: FilterConfig = (() => {
		const filters: FilterConfig = {};
		for (const [key, value] of initialSearchParams.entries()) {
			if (key.startsWith('filter[') && key.endsWith(']')) {
				const filterKey = key.slice(7, -1); // Remove 'filter[' and ']'
				filters[filterKey] = {
					value: value,
					operator: 'contains'
				};
			}
		}
		return filters;
	})();

	const initialSort: SortConfig = (() => {
		const sortParam = initialSearchParams.get('sort');
		if (!sortParam) return { column: null, direction: null };

		const direction = sortParam.startsWith('-') ? 'desc' : 'asc';
		const column = sortParam.startsWith('-') ? sortParam.slice(1) : sortParam;
		return { column, direction };
	})();

	const initialCount: string[] = (() => {
		const countParam = initialSearchParams.get('count');
		if (!countParam) return [];
		return countParam.split(',');
	})();

	if (initialSearchParams.get('page')) {
		initialPagination.currentPage = Number(initialSearchParams.get('page'));
	}

	let state: DataTableState<T> = $state({
		data: initialData,
		include: initialInclude,
		pagination: { ...initialPagination } as PaginationConfig,
		filters: { ...initialFilters },
		sort: { ...initialSort },
		count: initialCount,
		loading: false,
		selectedRows: new Set()
	});

	let isMounted = false;
	const visibleColumns = $derived(config.columns.filter((col) => col.visible !== false));
	const siblingCount = $derived(config.paginationSiblingCount?.desktop || 5);
	const mobileSiblingCount = $derived(config.paginationSiblingCount?.mobile || 2);
	const hasData = $derived(state.data.length > 0);
	const isLoading = $derived(state.loading || config.loading || !isMounted);

	async function fetchData(params: ApiRequestParams): Promise<ApiResponse<T>> {
		const url = new URL(config.apiEndpoint, window.location.origin);
		// Add pagination params
		url.searchParams.set('page', params.page.toString());
		// Add sorting params
		if (params.sort?.column && params.sort?.direction) {
			url.searchParams.set(
				'sort',
				`${params.sort.direction == 'desc' ? '-' : ''}${params.sort.column}`
			);
		}
		// Add include params
		if (params.include && params.include.length > 0) {
			url.searchParams.set(
				'include',
				Array.isArray(params.include) ? params.include.join(',') : params.include
			);
		}
		// Add filters params
		if (params.filters) {
			Object.entries(params.filters).forEach(([key, filter]) => {
				url.searchParams.set(
					`filter[${key}]`,
					Array.isArray(filter.value) ? filter.value.join(',') : filter.value
				);
			});
		}
		// Add count params
		if (params.count && params.count.length > 0) {
			url.searchParams.set('count', params.count.join(','));
		}
		console.log('fetchdata:params', params);
		console.log('fetchdata:url', url.toString());
		try {
			const response = await fetch(url.toString());
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}
			const result: ApiResponse<T> = await response.json();
			replaceState(window.location.pathname + '?' + url.searchParams.toString(), {});
			return result;
		} catch (error) {
			console.error('Error fetching data:', error);
			throw error;
		}
	}

	// Load data function
	async function loadData() {
		state.loading = true;
		try {
			const params: ApiRequestParams = {
				page: state.pagination.currentPage,
				// perPage: state.pagination.perPage,
				include: Array.isArray(state.include) ? state.include : [state.include],
				sort: state.sort.column ? state.sort : undefined,
				filters: Object.keys(state.filters).length > 0 ? state.filters : undefined,
				count: state.count ? state.count : undefined
			};
			const response = await fetchData(params);
			state.data = response.data.map((item) => item.attributes);
			state.pagination = {
				currentPage: response.meta.current_page,
				from: response.meta.from,
				to: response.meta.to,
				perPage: response.meta.per_page,
				lastPage: response.meta.last_page,
				total: response.meta.total
			};
			onDataChange?.(state.data);
			onPaginationChange?.(state.pagination);
		} catch (error) {
			console.error('Failed to load data:', error);
			// Keep existing data on error
		} finally {
			state.loading = false;
		}
	}

	// Event handlers
	function handleSort(column: string) {
		const columnDef = config.columns.find((col) => col.key === column);
		if (!columnDef?.sortable) {
			return;
		}
		let newDirection: SortDirection = 'asc';
		if (state.sort.column === column) {
			if (state.sort.direction === 'asc') {
				newDirection = 'desc';
			} else if (state.sort.direction === 'desc') {
				newDirection = null;
			}
		}
		state.sort = {
			column: newDirection ? column : null,
			direction: newDirection
		};
		onSortChange?.(state.sort);
		loadData();
	}

	function handleFilter(filters: FilterConfig) {
		state.filters = filters;
		state.pagination.currentPage = 1; // Reset to first page
		onFilterChange?.(filters);
		loadData();
	}

	function handlePaginationChange(pagination: PaginationConfig) {
		state.pagination = pagination;
		onPaginationChange?.(pagination);
		loadData();
	}

	function handleRowSelect(rowId: string | number, selected: boolean) {
		const newSelectedRows = new Set(state.selectedRows);
		if (selected) {
			newSelectedRows.add(rowId);
		} else {
			newSelectedRows.delete(rowId);
		}
		state.selectedRows = newSelectedRows;
		onRowSelect?.(newSelectedRows);
	}

	function handleSelectAll(selected: boolean) {
		if (selected) {
			state.selectedRows = new Set(state.data.map((row, index) => row.id || index));
		} else {
			state.selectedRows = new Set();
		}
		onRowSelect?.(state.selectedRows);
	}

	// Initialize data on mount
	onMount(() => {
		if (config.apiEndpoint && initialData.length === 0) {
			loadData();
		}
		isMounted = true;
	});
</script>

<div class="w-full min-w-0 space-y-5 {className}">
	{#if isLoading}
		<Skeleton class="h-4 w-52" />
	{/if}
	{#if hasData || state.pagination.total > 0}
		<ResultSummary pagination={state.pagination} />
	{/if}

	<div class="relative">
		{#if isLoading}
			<DataTableLoading />
		{/if}

		<div class="rounded-sm border">
			<Table.Root>
				<Table.Header>
					<DataTableHeader
						columns={visibleColumns}
						sort={state.sort}
						selectable={config.selectable}
						allSelected={state.selectedRows.size === state.data.length && state.data.length > 0}
						onSort={handleSort}
						onSelectAll={handleSelectAll}
						className={config.headerClassName}
						headerCellClassName={config.headerCellClassName}
					/>
				</Table.Header>
				<Table.Body>
					{#if hasData}
						<DataTableBody
							data={state.data}
							columns={visibleColumns}
							selectable={config.selectable}
							selectedRows={state.selectedRows}
							onRowSelect={handleRowSelect}
							className={config.bodyClassName}
							rowClassName={config.rowClassName}
							cellClassName={config.cellClassName}
						/>
					{:else if !isLoading}
						<DataTableEmpty message={config.emptyMessage || 'No data available'} />
					{/if}
				</Table.Body>
			</Table.Root>
		</div>
	</div>

	{#if hasData || state.pagination.total > 0}
		<DataTablePagination
			pagination={state.pagination}
			{siblingCount}
			{mobileSiblingCount}
			onChange={handlePaginationChange}
		/>
	{/if}
</div>
