import { describe, it, expect, beforeEach, vi } from 'vitest';
import { AuthService } from '../auth.service';
import { AuthRepository } from '../../repositories/auth.repository';
import { AuthValidator } from '../../validation/auth.validator';
import type { LoginRequestDTO, LoginResponseDTO } from '../../dtos/auth.dto';
import type { ApiResponse } from '../../types';

describe('AuthService', () => {
  let authService: AuthService;
  let mockAuthRepository: AuthRepository;
  let mockAuthValidator: AuthValidator;

  beforeEach(() => {
    mockAuthRepository = new AuthRepository();
    mockAuthValidator = new AuthValidator();
    authService = new AuthService();

    // Mock repository methods
    vi.spyOn(mockAuthRepository, 'login').mockImplementation(async () => ({
      data: {
        token: 'test-token',
        user: {
          id: 1,
          email: 'test@example.com',
          name: 'Test User',
        },
      },
      status: 200,
    }));

    vi.spyOn(mockAuthRepository, 'getCurrentUser').mockImplementation(async () => ({
      data: {
        id: 1,
        email: 'test@example.com',
        name: 'Test User',
      },
      status: 200,
    }));
  });

  describe('login', () => {
    it('should successfully login and store token', async () => {
      const loginData: LoginRequestDTO = {
        email: 'test@example.com',
        password: 'password123',
      };

      const result = await authService.login(loginData);

      expect(result).toBeDefined();
      expect(result.user).toBeDefined();
      expect(result.token).toBe('test-token');
      expect(localStorage.setItem).toHaveBeenCalledWith('auth_token', 'test-token');
    });

    it('should throw error for invalid login data', async () => {
      const loginData: LoginRequestDTO = {
        email: 'invalid-email',
        password: '123', // Too short
      };

      await expect(authService.login(loginData)).rejects.toThrow();
      expect(localStorage.setItem).not.toHaveBeenCalled();
    });
  });

  describe('logout', () => {
    it('should remove token on logout', async () => {
      await authService.logout();
      expect(localStorage.removeItem).toHaveBeenCalledWith('auth_token');
    });
  });

  describe('getCurrentUser', () => {
    it('should return current user when token exists', async () => {
      vi.spyOn(localStorage, 'getItem').mockReturnValue('test-token');

      const user = await authService.getCurrentUser();

      expect(user).toBeDefined();
      expect(user?.email).toBe('test@example.com');
    });

    it('should return null when no token exists', async () => {
      vi.spyOn(localStorage, 'getItem').mockReturnValue(null);

      const user = await authService.getCurrentUser();

      expect(user).toBeNull();
    });
  });

  describe('isAuthenticated', () => {
    it('should return true when token exists', () => {
      vi.spyOn(localStorage, 'getItem').mockReturnValue('test-token');
      expect(authService.isAuthenticated()).toBe(true);
    });

    it('should return false when no token exists', () => {
      vi.spyOn(localStorage, 'getItem').mockReturnValue(null);
      expect(authService.isAuthenticated()).toBe(false);
    });
  });
}); 