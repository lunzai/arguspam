import type { ApiResponse, PaginatedResponse } from '../types';
import { apiClient } from '../client';

export abstract class BaseRepository<T, CreateDTO = Partial<T>, UpdateDTO = Partial<T>> {
    protected abstract endpoint: string;

    // Get all items with pagination
    async getAll(params?: Record<string, any>): Promise<PaginatedResponse<T>> {
        return apiClient.get<PaginatedResponse<T>>(this.endpoint, { params });
    }

    // Get single item by ID
    async getById(id: number): Promise<ApiResponse<T>> {
        return apiClient.get<ApiResponse<T>>(`${this.endpoint}/${id}`);
    }

    // Create new item
    async create(data: CreateDTO): Promise<ApiResponse<T>> {
        return apiClient.post<ApiResponse<T>>(this.endpoint, data);
    }

    // Update item
    async update(id: number, data: UpdateDTO): Promise<ApiResponse<T>> {
        return apiClient.put<ApiResponse<T>>(`${this.endpoint}/${id}`, data);
    }

    // Delete item
    async delete(id: number): Promise<ApiResponse<void>> {
        return apiClient.delete<ApiResponse<void>>(`${this.endpoint}/${id}`);
    }

    // Custom query method
    protected async query<R>(path: string, config?: any): Promise<ApiResponse<R>> {
        const { method = 'GET', data, ...restConfig } = config || {};
        const url = `${this.endpoint}/${path}`;

        switch (method.toUpperCase()) {
            case 'POST':
                return apiClient.post<ApiResponse<R>>(url, data, restConfig);
            case 'PUT':
                return apiClient.put<ApiResponse<R>>(url, data, restConfig);
            case 'DELETE':
                return apiClient.delete<ApiResponse<R>>(url, restConfig);
            case 'GET':
            default:
                return apiClient.get<ApiResponse<R>>(url, { ...restConfig, params: data });
        }
    }
} 