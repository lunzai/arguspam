import { BaseService, type BaseFilterParams, type BaseFindByIdParams } from './base.js';
import type { User } from '$models/user.js';
import type { ApiResponse } from '$types/api.js';
import { clientApi } from '$lib/api/client.js';	
import type { Org } from '$models/org.js'

export interface UserFilterParams extends BaseFilterParams {
	filter?: {
		name?: string;
		email?: string;
		status?: 'active' | 'inactive';
		two_factor_enabled?: boolean;
	};
}

export interface UserFindByIdParams extends BaseFindByIdParams {
	include?: string[]; // No specific relationships defined in the model, so keeping generic
}

// Using generic create/update requests since the specific types are commented out in the model
export interface CreateUserRequest {
	[key: string]: any;
}

export interface UpdateUserRequest {
	[key: string]: any;
}

/**
 * User service for CRUD operations
 */
export class UserService extends BaseService<User, CreateUserRequest, UpdateUserRequest> {
	constructor() {
		super('/users');
	}

	async getOrgs(): Promise<ApiResponse<Org[]>> {
		return clientApi.get(`${this.endpoint}/me/orgs`);
	}

	async checkOrgAccess(orgId: number): Promise<boolean> {
		try {
			await clientApi.get(`${this.endpoint}/me/orgs/${orgId}`);
			return true;
		} catch (error) {
			return false;
		}
	}
	
	/**
	 * Find all users with filtering, sorting, and pagination
	 * @param params - Query parameters including filters, pagination, includes, and sorting
	 * @example
	 * ```ts
	 * // Get users filtered by status, sorted by name desc
	 * await userService.findAll({
	 *   filter: {
	 *     status: 'active',
	 *     name: 'john'
	 *   },
	 *   sort: ['-name'],
	 *   page: 1
	 * });
	 * ```
	 */
	async findAll(params: UserFilterParams = {}): Promise<ApiResponse<User[]>> {
		return super.findAll(params);
	}

	/**
	 * Find user by ID with optional relationship includes
	 * @param id - User ID
	 * @param params - Optional parameters including relationships to include
	 * @example
	 * ```ts
	 * // Get user by ID
	 * await userService.findById('1');
	 * ```
	 */
	async findById(id: string | number, params: UserFindByIdParams = {}): Promise<ApiResponse<User>> {
		return super.findById(id, params);
	}

	/**
	 * Create a new user
	 * @param data - User data to create
	 * @example
	 * ```ts
	 * await userService.create({
	 *   name: 'John Doe',
	 *   email: 'john@example.com',
	 *   status: 'active'
	 * });
	 * ```
	 */
	async create(data: CreateUserRequest): Promise<ApiResponse<User>> {
		return super.create(data);
	}

	/**
	 * Update an existing user
	 * @param id - User ID
	 * @param data - Updated user data
	 * @example
	 * ```ts
	 * await userService.update('1', {
	 *   name: 'John Smith',
	 *   status: 'inactive'
	 * });
	 * ```
	 */
	async update(id: string | number, data: UpdateUserRequest): Promise<ApiResponse<User>> {
		return super.update(id, data);
	}

	/**
	 * Delete a user
	 * @param id - User ID
	 * @example
	 * ```ts
	 * await userService.delete('1');
	 * ```
	 */
	async delete(id: string | number): Promise<void> {
		return super.delete(id);
	}
}

export const userService = new UserService(); 