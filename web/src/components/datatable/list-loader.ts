import type { BaseModel } from '$lib/models/base-model';
import type { BaseService, BaseFilterParams } from '$lib/services/base';
import type { ApiMeta } from '$lib/resources/api';
import { parseListParams } from './helper';
import type { SuperValidated } from 'sveltekit-superforms';

export interface CreateListLoadOptions<T extends BaseModel = BaseModel> {
	/** Factory to create the service instance. Receives authToken and currentOrgId from locals. */
	serviceFactory: (token: string, orgId: number) => BaseService<T>;
	/** RBAC check to run before loading. Receives me from locals. Throws if unauthorized. */
	rbacCheck: (me: any) => void;
	/** Default params merged with URL params. URL params override for scalar values. */
	defaultParams?: Partial<BaseFilterParams>;
	/** SvelteKit depends() key for invalidation. */
	depends?: string;
	/** Page title. */
	title?: string;
	/** Forms to pre-populate. */
	forms?: {
		[key: string]: SuperValidated<any>;
	};
}

/**
 * Create a reusable PageServerLoad for CRUD listing pages.
 * Parses url.searchParams, merges with defaultParams, calls service.findAll, returns { list, meta, title }.
 * Return type is compatible with PageServerLoad when assigned in +page.server.ts.
 */
export function createListLoad<T extends BaseModel = BaseModel>(options: CreateListLoadOptions<T>) {
	const { serviceFactory, rbacCheck, defaultParams = {}, depends, title = '', forms = [] } = options;

	return async ({
		url,
		locals,
		depends: dependsFn,
	}: {
		url: URL;
		locals: App.Locals;
		depends: (key: string) => void;
	}) => {
		if (depends && dependsFn) {
			dependsFn(depends);
		}

		rbacCheck(locals.me);

		const token = locals.authToken as string;
		const orgId = locals.currentOrgId as number;
		const service = serviceFactory(token, orgId);

		const urlParams = parseListParams(url);
		const mergedParams: BaseFilterParams = {
			...defaultParams,
			...urlParams,
			filter: {
				...(defaultParams.filter ?? {}),
				...(urlParams.filter ?? {})
			}
		};

		const response = await service.findAll(mergedParams);

		return {
			title,
			list: response.data,
			forms,
			meta: (response.meta ?? {
				current_page: 1,
				from: 0,
				last_page: 1,
				per_page: 20,
				to: 0,
				total: 0
			}) as ApiMeta
		};
	};
}
