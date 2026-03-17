import type { Table } from '@tanstack/table-core';
import type { BaseFilterParams } from '$lib/services/base';

export interface ListUrlParams {
    page?: number;
    perPage?: number;
    sort?: string[];
    include?: string[];
    filter?: Record<string, string | string[]>;
    count?: string[];
}

/**
 * Parse URL searchParams into BaseFilterParams. Handles filter[key]=value format.
 * Only includes keys that are present in the URL so merge with defaultParams works correctly.
 */
export function parseListParams(url: URL): Partial<BaseFilterParams> {
	const params = url.searchParams;
	const filter: Record<string, string> = {};
	for (const [key, value] of params.entries()) {
		const match = key.match(/^filter\[(.+)\]$/);
		if (match) filter[match[1]] = value;
	}
	const result: Partial<BaseFilterParams> = {};
	if (params.has('page')) result.page = Number(params.get('page')) || 1;
	if (params.has('per_page')) result.perPage = Number(params.get('per_page')) || 20;
	if (params.has('sort')) result.sort = params.get('sort')!.split(',').filter(Boolean);
	if (params.has('include')) result.include = params.get('include')!.split(',').filter(Boolean);
	if (Object.keys(filter).length) result.filter = filter;
	if (params.has('count')) result.count = params.get('count')!.split(',').filter(Boolean);
	return result;
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
	const filterToUse: Record<string, string> = Object.keys(filter).length
		? filter
		: base.filter
			? Object.fromEntries(
					Object.entries(base.filter).map(([k, v]) => [k, String(Array.isArray(v) ? v.join(',') : v)])
				)
			: {};
	for (const [key, value] of Object.entries(filterToUse)) {
		params.set(`filter[${key}]`, value);
	}

	return params;
}

