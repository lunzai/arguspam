import { Validator } from './validator';
import type { ValidationResult } from './types';
import type { LoginRequestDTO } from '../dtos/auth.dto';

export class AuthValidator extends Validator {
    // Validate login request
    public validateLogin(data: LoginRequestDTO): ValidationResult {
        this.clearErrors();

        // Required fields
        this.required(data.email, 'email');
        this.required(data.password, 'password');

        // Email format
        if (data.email) {
            this.email(data.email, 'email');
        }

        // Password length
        if (data.password) {
            this.minLength(data.password, 6, 'password');
        }

        return {
            isValid: !this.hasErrors(),
            errors: this.getErrors()
        };
    }
} 