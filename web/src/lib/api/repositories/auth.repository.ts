import type { ApiResponse } from '../types';
import type { LoginRequestDTO, LoginResponseDTO } from '../dtos/auth.dto';
import { BaseRepository } from './base.repository';
import { apiClient, createServerApiClient } from '../client';

export class AuthRepository extends BaseRepository<any> {
    protected endpoint = 'auth';
    private client: any;

    constructor(serverClient?: any) {
        super();
        this.client = serverClient || apiClient;
    }

    // Login user
    async login(data: LoginRequestDTO): Promise<ApiResponse<LoginResponseDTO>> {
        return this.client.post(`${this.endpoint}/login`, data);
    }

    // Logout user
    async logout(): Promise<ApiResponse<void>> {
        return this.client.post(`${this.endpoint}/logout`);
    }

    // Static method for server-side login
    static async serverLogin(data: LoginRequestDTO): Promise<ApiResponse<LoginResponseDTO>> {
        const serverClient = createServerApiClient();
        const repository = new AuthRepository(serverClient);
        return repository.login(data);
    }
} 