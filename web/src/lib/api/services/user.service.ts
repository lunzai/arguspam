import type { User } from '../types';
import type { CreateUserDTO, UpdateUserDTO } from '../dtos/user.dto';
import { BaseService } from './base.service';
import { UserRepository } from '../repositories/user.repository';
import { UserValidator } from '../validation/user.validator';
import type { ValidationResult } from '../validation/types';

export class UserService extends BaseService<User> {
    protected repository = new UserRepository();
    private validator = new UserValidator();

    // Validate user creation
    public validateCreate(data: CreateUserDTO): ValidationResult {
        return this.validator.validateCreate(data);
    }

    // Validate user update
    public validateUpdate(data: UpdateUserDTO): ValidationResult {
        return this.validator.validateUpdate(data);
    }

    // Create user with validation
    async create(data: CreateUserDTO): Promise<User> {
        const validation = this.validateCreate(data);
        if (!validation.isValid) {
            throw new Error(JSON.stringify(validation.errors));
        }
        return super.create(data);
    }

    // Update user with validation
    async update(id: number, data: UpdateUserDTO): Promise<User> {
        const validation = this.validateUpdate(data);
        if (!validation.isValid) {
            throw new Error(JSON.stringify(validation.errors));
        }
        return super.update(id, data);
    }

    // Get current authenticated user
    async getCurrentUser(): Promise<User> {
        try {
            const response = await this.repository.getCurrentUser();
            
            // Extract user data from attributes (Laravel Resource format)
            const responseData = response.data as any;
            const userData = responseData.attributes as User;
            return userData;
        } catch (error) {
            return this.handleError(error);
        }
    }

    // Update user profile
    async updateProfile(data: UpdateUserDTO): Promise<User> {
        const validation = this.validateUpdate(data);
        if (!validation.isValid) {
            throw new Error(JSON.stringify(validation.errors));
        }
        try {
            const response = await this.repository.updateProfile(data);
            return this.transformResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    // Get user's organizations
    async getUserOrganizations(): Promise<User> {
        try {
            const response = await this.repository.getUserOrganizations();
            return this.transformResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    // Business logic methods
    async validateUserEmail(email: string): Promise<boolean> {
        // Add your email validation logic here
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
} 