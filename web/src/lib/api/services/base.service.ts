import type { ApiResponse } from '../types';
import { BaseRepository } from '../repositories/base.repository';

export abstract class BaseService<T> {
    protected abstract repository: BaseRepository<T>;

    // Transform API response to domain model
    protected transformResponse<R>(response: ApiResponse<R>): R {
        return response.data;
    }

    // Handle errors
    protected handleError(error: any): never {
        // You can customize error handling here
        console.error('API Error:', error);
        throw error;
    }

    // Get all items
    async getAll(params?: Record<string, any>) {
        try {
            const response = await this.repository.getAll(params);
            return this.transformResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    // Get item by ID
    async getById(id: number) {
        try {
            const response = await this.repository.getById(id);
            return this.transformResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    // Create item
    async create(data: any) {
        try {
            const response = await this.repository.create(data);
            return this.transformResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    // Update item
    async update(id: number, data: any) {
        try {
            const response = await this.repository.update(id, data);
            return this.transformResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    // Delete item
    async delete(id: number) {
        try {
            const response = await this.repository.delete(id);
            return this.transformResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }
} 