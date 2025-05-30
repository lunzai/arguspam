import type { ApiResponse, User } from '../types';
import { BaseRepository } from './base.repository';

interface CreateUserDTO {
    name: string;
    email: string;
    password: string;
}

interface UpdateUserDTO {
    name?: string;
    email?: string;
    password?: string;
}

export class UserRepository extends BaseRepository<User, CreateUserDTO, UpdateUserDTO> {
    protected endpoint = 'users';

    // Get current authenticated user
    async getCurrentUser(): Promise<ApiResponse<User>> {
        return this.query<User>('me');
    }

    // Update user profile
    async updateProfile(data: UpdateUserDTO): Promise<ApiResponse<User>> {
        return this.query<User>('profile', { method: 'PUT', data });
    }

    // Get user's organizations
    async getUserOrganizations(): Promise<ApiResponse<User>> {
        return this.query<User>('organizations');
    }
} 