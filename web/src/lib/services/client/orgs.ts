import { BaseService, type BaseFilterParams, type BaseFindByIdParams } from './base.js';
import type { Org, CreateOrgRequest, UpdateOrgRequest } from '$models/org.js';
import type { ApiResourceResponse, ApiCollectionResponse } from '$types/api.js';
import { clientApi } from '$lib/api/client.js';

export interface OrgFilterParams extends BaseFilterParams {
	filter?: {
		name?: string;
		status?: 'active' | 'inactive';
		description?: string;
	};
}

export interface OrgFindByIdParams extends BaseFindByIdParams {
	include?: ('users' | 'userGroups')[];
}

/**
 * Organization service for CRUD operations
 */
export class OrgService extends BaseService<Org, CreateOrgRequest, UpdateOrgRequest> {
	constructor() {
		super('/orgs');
	}

	/**
	 * Find all organizations with filtering, sorting, and pagination
	 * @param params - Query parameters including filters, pagination, includes, and sorting
	 * @example
	 * ```ts
	 * // Get organizations with users included, filtered by status, sorted by name desc
	 * await orgService.findAll({
	 *   include: ['users', 'userGroups'],
	 *   filter: {
	 *     status: 'active',
	 *     name: 'acme'
	 *   },
	 *   sort: ['-name'], // descending order
	 *   page: 1
	 * });
	 * ```
	 */
	async findAll(params: OrgFilterParams = {}): Promise<ApiCollectionResponse<Org>> {
		return super.findAll(params);
	}

	/**
	 * Find organization by ID with optional relationship includes
	 * @param id - Organization ID
	 * @param params - Optional parameters including relationships to include
	 * @example
	 * ```ts
	 * // Get organization with users and user groups
	 * await orgService.findById('1', { include: ['users', 'userGroups'] });
	 * ```
	 */
	async findById(id: string | number, params: OrgFindByIdParams = {}): Promise<ApiResourceResponse<Org>> {
		return super.findById(id, params);
	}

	/**
	 * Create a new organization
	 * @param data - Organization data to create
	 * @example
	 * ```ts
	 * await orgService.create({
	 *   name: 'ACME Corp',
	 *   description: 'Technology company',
	 *   status: 'active'
	 * });
	 * ```
	 */
	async create(data: CreateOrgRequest): Promise<ApiResourceResponse<Org>> {
		return super.create(data);
	}

	/**
	 * Update an existing organization
	 * @param id - Organization ID
	 * @param data - Updated organization data
	 * @example
	 * ```ts
	 * await orgService.update('1', {
	 *   name: 'ACME Corporation',
	 *   status: 'inactive'
	 * });
	 * ```
	 */
	async update(id: string | number, data: UpdateOrgRequest): Promise<ApiResourceResponse<Org>> {
		return super.update(id, data);
	}

	/**
	 * Delete an organization
	 * @param id - Organization ID
	 * @example
	 * ```ts
	 * await orgService.delete('1');
	 * ```
	 */
	async delete(id: string | number): Promise<void> {
		return super.delete(id);
	}
}

export const orgService = new OrgService(); 