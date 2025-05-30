import type { ApiResponse } from '../types';
import type { LoginRequestDTO, LoginResponseDTO } from '../dtos/auth.dto';
import { BaseRepository } from './base.repository';

export class AuthRepository extends BaseRepository<any> {
    protected endpoint = 'auth';

    // Login user
    async login(data: LoginRequestDTO): Promise<ApiResponse<LoginResponseDTO>> {
        return this.query<LoginResponseDTO>('login', {
            method: 'POST',
            data
        });
    }

    // Logout user
    async logout(): Promise<ApiResponse<void>> {
        return this.query<void>('logout', {
            method: 'POST'
        });
    }

    // Get current user
    async getCurrentUser(): Promise<ApiResponse<any>> {
        return this.query<any>('me');
    }
} 