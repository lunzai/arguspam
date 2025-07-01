import { ServerApi } from '$lib/api/server';
import type { ApiCollectionResponse, ApiResourceResponse } from '$resources/api';
import type { BaseModel } from '$models/base-model';

export interface BaseFilterParams {
	page?: number;
	include?: string[];
	sort?: string[];
	filter?: Record<string, any>;
	count?: string[];
}

export interface BaseFindByIdParams {
	include?: string[];
}

export interface BaseUpdateRequest {
	[key: string]: any;
}

export interface BaseCreateRequest {
	[key: string]: any;
}

/**
 * Base service class with common CRUD operations
 */
export abstract class BaseService<
	TModel extends BaseModel,
	TCreateRequest extends BaseCreateRequest = BaseCreateRequest,
	TUpdateRequest extends BaseUpdateRequest = BaseUpdateRequest
> {
	protected readonly endpoint: string;
	protected readonly api: ServerApi;
	protected readonly currentOrgId: number | null;
	protected readonly token: string | null;

	constructor(endpoint: string, token?: string | null, currentOrgId?: number | null) {
		this.endpoint = endpoint;
		this.currentOrgId = currentOrgId || null;
		this.token = token || null;
		this.api = new ServerApi(this.token, this.currentOrgId);
	}

	/**
	 * Build query parameters for API requests
	 */
	protected buildQueryParams(params: BaseFilterParams = {}): string {
		const queryParams = new URLSearchParams();
		if (params.page) queryParams.set('page', params.page.toString());
		if (params.include && params.include.length > 0) {
			queryParams.set('include', params.include.join(','));
		}
		if (params.sort && params.sort.length > 0) {
			queryParams.set('sort', params.sort.join(','));
		}
		if (params.filter) {
			Object.keys(params.filter).forEach((key) => {
				queryParams.set(`filter[${key}]`, params.filter![key]);
			});
		}
		if (params.count && params.count.length > 0) {
			queryParams.set('count', params.count.join(','));
		}
		return queryParams.toString();
	}

	/**
	 * Find all records with optional filtering, sorting, and pagination
	 */
	async findAll(params: BaseFilterParams = {}): Promise<ApiCollectionResponse<TModel>> {
		const queryString = this.buildQueryParams(params);
		const url = queryString ? `${this.endpoint}?${queryString}` : this.endpoint;
		return this.api.get<ApiCollectionResponse<TModel>>(url);
	}

	/**
	 * Find a record by ID with optional includes
	 */
	async findById(
		id: string | number,
		params: BaseFindByIdParams = {}
	): Promise<ApiResourceResponse<TModel>> {
		const queryString = this.buildQueryParams(params);
		const url = queryString ? `${this.endpoint}/${id}?${queryString}` : `${this.endpoint}/${id}`;
		return this.api.get<ApiResourceResponse<TModel>>(url);
	}

	/**
	 * Create a new record
	 */
	async create(data: TCreateRequest): Promise<ApiResourceResponse<TModel>> {
		return this.api.post<ApiResourceResponse<TModel>>(this.endpoint, data);
	}

	/**
	 * Update an existing record
	 */
	async update(id: string | number, data: TUpdateRequest): Promise<ApiResourceResponse<TModel>> {
		return this.api.put<ApiResourceResponse<TModel>>(`${this.endpoint}/${id}`, data);
	}

	/**
	 * Delete a record
	 */
	async delete(id: string | number): Promise<void> {
		return this.api.delete<void>(`${this.endpoint}/${id}`);
	}

	/**
	 * Create a new service instance with different org context
	 */
	withOrg(currentOrgId: number): BaseService<TModel, TCreateRequest, TUpdateRequest> {
		// This is abstract, so we'll return a new instance using the current constructor
		return new (this.constructor as any)(this.endpoint, currentOrgId, this.token);
	}

	/**
	 * Create a new service instance with different token
	 */
	withToken(token: string): BaseService<TModel, TCreateRequest, TUpdateRequest> {
		return new (this.constructor as any)(this.endpoint, this.currentOrgId, token);
	}

	/**
	 * Create a new service instance without org context (for public endpoints)
	 */
	withoutOrg(): BaseService<TModel, TCreateRequest, TUpdateRequest> {
		return new (this.constructor as any)(this.endpoint, undefined, this.token);
	}

	/**
	 * Create a new service instance without token (for public endpoints)
	 */
	withoutToken(): BaseService<TModel, TCreateRequest, TUpdateRequest> {
		return new (this.constructor as any)(this.endpoint, this.currentOrgId, undefined);
	}
}
