import { BaseService } from './base.service';
import { AuthRepository } from '../repositories/auth.repository';
import { AuthValidator } from '../validation/auth.validator';
import type { LoginRequestDTO, LoginResponseDTO } from '../dtos/auth.dto';
import type { ValidationResult } from '../validation/types';
import { browser } from '$app/environment';

export class AuthService extends BaseService<LoginResponseDTO> {
    protected repository: AuthRepository;
    private validator: AuthValidator;

    constructor() {
        super();
        this.repository = new AuthRepository(); // Will use client-side apiClient by default
        this.validator = new AuthValidator();
    }

    // Validate login request
    public validateLogin(data: LoginRequestDTO): ValidationResult {
        return this.validator.validateLogin(data);
    }

    // Logout user
    public async logout(): Promise<void> {
        try {
            await this.repository.logout();
        } catch (error) {
            console.error('Logout error:', error);
            throw error;
        }
    }

    // Get auth token for API calls
    public async getAuthToken(): Promise<string | null> {
        if (!browser) return null;
        
        try {
            const response = await fetch('/api/auth/token', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                return data.token;
            }
            
            return null;
        } catch (error) {
            return null;
        }
    }

    // Get auth header for API requests
    public async getAuthHeader(): Promise<Record<string, string>> {
        const token = await this.getAuthToken();
        return token ? { Authorization: `Bearer ${token}` } : {};
    }
} 