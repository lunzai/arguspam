import DataTable from './data-table.svelte';
import DataTableHeader from './components/header.svelte';
import DataTableBody from './components/body.svelte';
import DataTableRow from './components/row.svelte';
import DataTableCell from './components/cell.svelte';
import DataTableHeaderCell from './components/header-cell.svelte';
import DataTablePagination from './components/pagination.svelte';
import DataTableFilter from './components/filter.svelte';
import DataTableEmpty from './components/empty.svelte';
import DataTableLoading from './components/loading.svelte';
import SimpleDataTable from './simple-data-table.svelte';
// import SimpleDataTableActions from './components/simple-data-table-actions.svelte';
import SimpleDataTableDeleteAction from './components/simple-data-table-delete-action.svelte';

export type {
	ColumnDefinition,
	SortDirection,
	FilterConfig,
	PaginationConfig,
	DataTableConfig,
	DataTableState,
	DataTableProps
} from './types';

export {
	DataTable,
	DataTableHeader,
	DataTableBody,
	DataTableRow,
	DataTableCell,
	DataTableHeaderCell,
	DataTablePagination,
	DataTableFilter,
	DataTableEmpty,
	DataTableLoading,
	SimpleDataTable,
	// SimpleDataTableActions,
	SimpleDataTableDeleteAction,
	
	DataTable as Root,
	DataTableHeader as Header,
	DataTableBody as Body,
	DataTableRow as Row,
	DataTableCell as Cell,
	DataTableHeaderCell as HeaderCell,
	DataTablePagination as Pagination,
	DataTableFilter as Filter,
	DataTableEmpty as Empty,
	DataTableLoading as Loading
};
