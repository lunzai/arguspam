import { BaseService } from './base.service';
import { AuthRepository } from '../repositories/auth.repository';
import { AuthValidator } from '../validation/auth.validator';
import type { LoginRequestDTO, LoginResponseDTO } from '../dtos/auth.dto';
import type { User } from '../types';
import type { ValidationResult } from '../validation/types';
import config from '../../config';
import { browser } from '$app/environment';

export class AuthService extends BaseService<LoginResponseDTO> {
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
    public async login(credentials: LoginRequestDTO): Promise<LoginResponseDTO> {
        // Validate login data
        const validationResult = this.validator.validateLogin(credentials);
        if (!validationResult.isValid) {
            throw new Error('Invalid login credentials');
        }

        try {
            const response = await this.repository.login(credentials);
            if (browser) {
                this.setCookie(
                    config.auth.tokenKey,
                    response.data.token,
                    config.auth.tokenExpiry,
                    config.auth.cookieOptions
                );
            }
            return response.data;
        } catch (error) {
            throw new Error('Invalid credentials');
        }
    }

    // Logout user
    public async logout(): Promise<void> {
        try {
            await this.repository.logout();
        } finally {
            if (browser) {
                this.removeCookie(config.auth.tokenKey, config.auth.cookieOptions);
            }
        }
    }

    // Get current user
    public async getCurrentUser(): Promise<User | null> {
        if (!this.isAuthenticated()) {
            return null;
        }

        try {
            const response = await this.repository.getCurrentUser();
            return response.data;
        } catch (error) {
            if (browser) {
                this.removeCookie(config.auth.tokenKey, config.auth.cookieOptions);
            }
            return null;
        }
    }

    // Check if user is authenticated
    public isAuthenticated(): boolean {
        if (!browser) return false;
        return this.getCookie(config.auth.tokenKey) !== null;
    }

    // Get auth token
    public getAuthHeader(): Record<string, string> {
        if (!browser) return {};
        const token = this.getCookie(config.auth.tokenKey);
        return token ? { Authorization: `Bearer ${token}` } : {};
    }

    private setCookie(
        name: string,
        value: string,
        maxAge: number,
        options: typeof config.auth.cookieOptions
    ): void {
        if (!browser) return;
        const { path, secure, sameSite } = options;
        document.cookie = `${name}=${value}; path=${path}; max-age=${maxAge}; secure=${secure}; samesite=${sameSite}`;
    }

    private removeCookie(
        name: string,
        options: typeof config.auth.cookieOptions
    ): void {
        if (!browser) return;
        const { path, secure, sameSite } = options;
        document.cookie = `${name}=; path=${path}; max-age=0; secure=${secure}; samesite=${sameSite}`;
    }

    private getCookie(name: string): string | null {
        if (!browser) return null;
        const cookies = document.cookie.split(';');
        const cookie = cookies.find(c => c.trim().startsWith(`${name}=`));
        return cookie ? cookie.split('=')[1] : null;
    }
} 