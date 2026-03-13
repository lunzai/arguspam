import type { Table } from '@tanstack/table-core';

export interface ListUrlParams {
    page?: number;
    perPage?: number;
    sort?: string[];
    include?: string[];
    filter?: Record<string, string | string[]>;
    count?: string[];
}

export function tableStateToUrlParams(table: Table<any>, base: Partial<ListUrlParams> = {}): URLSearchParams {
	const sort = table.getState().sorting.map((s) => (s.desc ? `-${s.id}` : s.id));
	const filter: Record<string, string> = {};
	for (const f of table.getState().columnFilters) {
		if (f.value !== undefined && f.value !== '' && f.value != null) {
			filter[f.id] = String(f.value);
		}
	}

	const params = new URLSearchParams();
	params.set('page', String(table.getState().pagination.pageIndex + 1));
	params.set('per_page', String(table.getState().pagination.pageSize));
	params.set('sort', sort.length ? sort.join(',') : base.sort?.join(',') || '');
	//params.set('filter', Object.keys(filter).length ? Object.fromEntries(Object.entries(filter).map(([key, value]) => [key, String(value)])) : base.filter ? Object.fromEntries(Object.entries(base.filter).map(([key, value]) => [key, String(value)])) : undefined);

	return params;
}

