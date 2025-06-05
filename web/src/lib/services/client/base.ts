import { clientApi } from '$lib/api/client.js';
import type { ApiResponse } from '$types/api.js';
import type { BaseModel } from '$models/base-model.js';

export interface BaseFilterParams {
	page?: number;
	include?: string[];
	sort?: string[];
	filter?: Record<string, any>;
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

	constructor(endpoint: string) {
		this.endpoint = endpoint;
	}

	/**
	 * Build query parameters for API requests
	 */
	protected buildQueryParams(params: BaseFilterParams = {}): string {
		const queryParams = new URLSearchParams();

		// Handle pagination
		if (params.page) queryParams.set('page', params.page.toString());

		// Handle include relationships
		if (params.include && params.include.length > 0) {
			queryParams.set('include', params.include.join(','));
		}

		// Handle sorting
		if (params.sort && params.sort.length > 0) {
			queryParams.set('sort', params.sort.join(','));
		}

		// Handle filter parameters (filter[name], filter[status], etc.)
		if (params.filter) {
			Object.keys(params.filter).forEach(key => {
				queryParams.set(`filter[${key}]`, params.filter![key]);
			});
		}

		return queryParams.toString();
	}

	/**
	 * Find all records with optional filtering, sorting, and pagination
	 */
	async findAll(params: BaseFilterParams = {}): Promise<ApiResponse<TModel[]>> {
		const queryString = this.buildQueryParams(params);
		const url = queryString ? `${this.endpoint}?${queryString}` : this.endpoint;
		return clientApi.get<ApiResponse<TModel[]>>(url);
	}

	/**
	 * Find a record by ID with optional includes
	 */
	async findById(id: string | number, params: BaseFindByIdParams = {}): Promise<ApiResponse<TModel>> {
		const queryString = this.buildQueryParams(params);
		const url = queryString ? `${this.endpoint}/${id}?${queryString}` : `${this.endpoint}/${id}`;
		return clientApi.get<ApiResponse<TModel>>(url);
	}

	/**
	 * Create a new record
	 */
	async create(data: TCreateRequest): Promise<ApiResponse<TModel>> {
		return clientApi.post<ApiResponse<TModel>>(this.endpoint, data);
	}

	/**
	 * Update an existing record
	 */
	async update(id: string | number, data: TUpdateRequest): Promise<ApiResponse<TModel>> {
		return clientApi.put<ApiResponse<TModel>>(`${this.endpoint}/${id}`, data);
	}

	/**
	 * Delete a record
	 */
	async delete(id: string | number): Promise<void> {
		return clientApi.delete<void>(`${this.endpoint}/${id}`);
	}
} 