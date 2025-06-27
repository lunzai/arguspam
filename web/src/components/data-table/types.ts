export type SortDirection = 'asc' | 'desc' | null;

export interface ColumnDefinition<T = any> {
	key: string;
	title: string;
	width?: string;
	sortable?: boolean;
	filterable?: boolean;
	visible?: boolean;
	type?: 'badge' | 'icon' | 'text' | 'boolean';
	booleanTrue?: string;
	booleanFalse?: string;
	emptyText?: string;
	componentProps?: (value: any, row: T, index: number) => Record<string, any>;
	renderer?: (value: any, row: T, index: number) => string | HTMLElement | null;
	headerRenderer?: (column: ColumnDefinition<T>) => string | HTMLElement | null;
	align?: 'left' | 'center' | 'right';
}

export interface FilterConfig {
	[key: string]: {
		value: string | string[];
		operator:
			| 'equals'
			| 'contains'
			| 'startsWith'
			| 'endsWith'
			| 'greaterThan'
			| 'lessThan'
			| 'between';
	};
}

export interface PaginationConfig {
	currentPage: number;
	from: number;
	to: number;
	perPage?: number;
	lastPage: number;
	total: number;
}

export interface SortConfig {
	column: string | null;
	direction: SortDirection;
}

export interface DataTableConfig<T = any> {
	model: T;
	columns: ColumnDefinition<T>[];
	apiEndpoint: string;
	// pageSize?: number;
	// pageSizeOptions?: number[];
	paginationSiblingCount?: { 
        desktop?: number; 
        mobile?: number 
    };
	sortable?: boolean;
	filterable?: boolean;
	selectable?: boolean;
	loading?: boolean;
	emptyMessage?: string;
	className?: string;
	headerClassName?: string;
	bodyClassName?: string;
	rowClassName?: string;
	cellClassName?: string;
	headerCellClassName?: string;
}

export interface DataTableState<T = any> {
	data: T[];
	include: string[];
	pagination: PaginationConfig;
	filters: FilterConfig;
	sort: SortConfig;
	loading: boolean;
	selectedRows: Set<string | number>;
}

export interface DataTableProps<T = any> {
    model: T;
	config: DataTableConfig<T>;
	initialInclude?: string[];
	initialData?: any[];
	initialPagination?: Partial<PaginationConfig>;
	initialSearchParams?: URLSearchParams;
	onDataChange?: (data: any[]) => void;
	onPaginationChange?: (pagination: PaginationConfig) => void;
	onFilterChange?: (filters: FilterConfig) => void;
	onSortChange?: (sort: { column: string | null; direction: SortDirection }) => void;
	onRowSelect?: (selectedRows: Set<string | number>) => void;
}

export interface ApiRequestParams {
	page: number;
	perPage?: number;
	sort?: SortConfig;
	filters?: FilterConfig;
	include?: string[];
}

// Same as ApiCollectionResponse in api.ts
export interface ApiResponse<T> {
	data: Collection<T>;
	meta: ApiMeta;
}

export interface ApiMeta {
	current_page: number;
	from: number;
	last_page: number;
	per_page: number;
	to: number;
	total: number;
}

export type Collection<T> = Resource<T>[];

export interface Resource<T> {
	attributes: T;
	relationships?: Record<string, Resource<any>[]>;
}

export interface CellBadge {
	value: string;
	variant: 'default' | 'secondary' | 'destructive' | 'outline';
	className?: string;
}