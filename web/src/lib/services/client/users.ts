import { clientApi } from '$lib/api/client.js';
import type { User, CreateUserRequest, UpdateUserRequest } from '$lib/types/user.js';
import type { ApiResponse } from '$lib/types/response.js';

export interface UsersResponse {
	data: User[];
	meta?: {
		total: number;
		per_page: number;
		current_page: number;
		last_page: number;
	};
}

/**
 * User service for direct backend API calls
 */
export class UserService {
	/**
	 * Get all users with pagination
	 */
	async getUsers(params?: {
		page?: number;
		per_page?: number;
		search?: string;
		status?: string;
	}): Promise<ApiResponse<User[]>> {
		const queryParams = new URLSearchParams();
		
		if (params?.page) queryParams.set('page', params.page.toString());
		if (params?.per_page) queryParams.set('per_page', params.per_page.toString());
		if (params?.search) queryParams.set('search', params.search);
		if (params?.status) queryParams.set('status', params.status);
		
		const query = queryParams.toString();
		const endpoint = query ? `/users?${query}` : '/users';
		
		return clientApi.get<ApiResponse<User[]>>(endpoint);
	}

	/**
	 * Get a specific user by ID
	 */
	async getUser(id: string): Promise<ApiResponse<User>> {
		return clientApi.get<ApiResponse<User>>(`/users/${id}`);
	}

	/**
	 * Create a new user
	 */
	async createUser(userData: CreateUserRequest): Promise<ApiResponse<User>> {
		return clientApi.post<ApiResponse<User>>('/users', userData);
	}

	/**
	 * Update an existing user
	 */
	async updateUser(id: string, userData: UpdateUserRequest): Promise<ApiResponse<User>> {
		return clientApi.put<ApiResponse<User>>(`/users/${id}`, userData);
	}

	/**
	 * Delete a user
	 */
	async deleteUser(id: string): Promise<void> {
		return clientApi.delete<void>(`/users/${id}`);
	}

	/**
	 * Get user roles
	 */
	async getUserRoles(id: string): Promise<ApiResponse<any[]>> {
		return clientApi.get<ApiResponse<any[]>>(`/users/${id}/roles`);
	}

	/**
	 * Assign roles to user
	 */
	async assignUserRoles(id: string, roleIds: number[]): Promise<void> {
		return clientApi.post<void>(`/users/${id}/roles`, { role_ids: roleIds });
	}

	/**
	 * Remove roles from user
	 */
	async removeUserRoles(id: string, roleIds: number[]): Promise<void> {
		return clientApi.post<void>(`/users/${id}/roles/remove`, { role_ids: roleIds });
	}
}

export const userService = new UserService(); 