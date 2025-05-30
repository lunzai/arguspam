import { Validator } from './validator';
import type { ValidationResult } from './types';
import type { CreateUserDTO, UpdateUserDTO } from '../dtos/user.dto';

export class UserValidator extends Validator {
    // Validate user creation
    public validateCreate(data: CreateUserDTO): ValidationResult {
        this.clearErrors();

        // Required fields
        this.required(data.name, 'name');
        this.required(data.email, 'email');
        this.required(data.password, 'password');

        // Email format
        if (data.email) {
            this.email(data.email, 'email');
        }

        // Name length
        if (data.name) {
            this.minLength(data.name, 2, 'name');
            this.maxLength(data.name, 50, 'name');
        }

        // Password strength
        if (data.password) {
            this.minLength(data.password, 8, 'password');
            this.passwordStrength(data.password, 'password');
        }

        return {
            isValid: !this.hasErrors(),
            errors: this.getErrors()
        };
    }

    // Validate user update
    public validateUpdate(data: UpdateUserDTO): ValidationResult {
        this.clearErrors();

        // Email format
        if (data.email) {
            this.email(data.email, 'email');
        }

        // Name length
        if (data.name) {
            this.minLength(data.name, 2, 'name');
            this.maxLength(data.name, 50, 'name');
        }

        // Password strength (if provided)
        if (data.password) {
            this.minLength(data.password, 8, 'password');
            this.passwordStrength(data.password, 'password');
        }

        return {
            isValid: !this.hasErrors(),
            errors: this.getErrors()
        };
    }
} 