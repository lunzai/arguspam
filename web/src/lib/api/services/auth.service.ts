import { apiClient } from '../client';
import type { LoginRequest, LoginResponse, ApiResponse } from '../types';

export class AuthService {
    private static instance: AuthService;
    private readonly baseUrl = '/auth';

    private constructor() {}

    public static getInstance(): AuthService {
        if (!AuthService.instance) {
            AuthService.instance = new AuthService();
        }
        return AuthService.instance;
    }

    public async login(credentials: LoginRequest): Promise<ApiResponse<LoginResponse>> {
        const response = await apiClient.post<ApiResponse<LoginResponse>>(
            `${this.baseUrl}/login`,
            credentials
        );
        
        if (response.data?.token) {
            localStorage.setItem('auth_token', response.data.token);
        }
        
        return response;
    }

    public async logout(): Promise<ApiResponse> {
        const response = await apiClient.post<ApiResponse>(`${this.baseUrl}/logout`);
        localStorage.removeItem('auth_token');
        return response;
    }

    public isAuthenticated(): boolean {
        return !!localStorage.getItem('auth_token');
    }
}

export const authService = AuthService.getInstance(); 