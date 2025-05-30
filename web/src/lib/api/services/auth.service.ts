import { BaseService } from './base.service';
import { AuthRepository } from '../repositories/auth.repository';
import { AuthValidator } from '../validation/auth.validator';
import type { LoginRequestDTO, LoginResponseDTO } from '../dtos/auth.dto';
import type { ValidationResult } from '../validation/types';

export class AuthService extends BaseService<LoginResponseDTO> {
    private readonly TOKEN_KEY = 'auth_token';
    protected repository: AuthRepository;
    private validator: AuthValidator;

    constructor() {
        super();
        this.repository = new AuthRepository();
        this.validator = new AuthValidator();
    }

    // Validate login request
    public validateLogin(data: LoginRequestDTO): ValidationResult {
        return this.validator.validateLogin(data);
    }

    // Login user
    public async login(data: LoginRequestDTO): Promise<LoginResponseDTO> {
        // Validate login data
        const validation = this.validateLogin(data);
        if (!validation.isValid) {
            throw new Error('Invalid login data');
        }

        try {
            const response = await this.repository.login(data);
            this.setToken(response.data.token);
            return response.data;
        } catch (error) {
            // Remove token if login fails
            this.removeToken();
            throw error;
        }
    }

    // Logout user
    public async logout(): Promise<void> {
        try {
            await this.repository.logout();
        } finally {
            // Always remove token on logout attempt
            this.removeToken();
        }
    }

    // Get current user
    public async getCurrentUser() {
        try {
            const response = await this.repository.getCurrentUser();
            return response.data;
        } catch (error) {
            // Remove token if getting current user fails
            this.removeToken();
            throw error;
        }
    }

    // Check if user is authenticated
    public isAuthenticated(): boolean {
        return !!this.getToken();
    }

    // Get auth token
    public getToken(): string | null {
        return localStorage.getItem(this.TOKEN_KEY);
    }

    // Set auth token
    protected setToken(token: string): void {
        localStorage.setItem(this.TOKEN_KEY, token);
    }

    // Remove auth token
    protected removeToken(): void {
        localStorage.removeItem(this.TOKEN_KEY);
    }

    // Get auth header
    public getAuthHeader(): Record<string, string> {
        const token = this.getToken();
        return token ? { Authorization: `Bearer ${token}` } : {};
    }
} 