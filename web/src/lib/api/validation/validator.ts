import type { ValidationError } from './types';

export class Validator {
    private errors: ValidationError[] = [];

    // Add validation error
    private addError(field: string, message: string): void {
        this.errors.push({ field, message });
    }

    // Clear all errors
    public clearErrors(): void {
        this.errors = [];
    }

    // Get all errors
    public getErrors(): ValidationError[] {
        return this.errors;
    }

    // Check if has errors
    public hasErrors(): boolean {
        return this.errors.length > 0;
    }

    // Validate required field
    public required(value: any, field: string, message?: string): boolean {
        if (value === undefined || value === null || value === '') {
            this.addError(field, message || `${field} is required`);
            return false;
        }
        return true;
    }

    // Validate email
    public email(value: string, field: string, message?: string): boolean {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            this.addError(field, message || 'Invalid email format');
            return false;
        }
        return true;
    }

    // Validate minimum length
    public minLength(value: string, min: number, field: string, message?: string): boolean {
        if (value.length < min) {
            this.addError(field, message || `${field} must be at least ${min} characters`);
            return false;
        }
        return true;
    }

    // Validate maximum length
    public maxLength(value: string, max: number, field: string, message?: string): boolean {
        if (value.length > max) {
            this.addError(field, message || `${field} must not exceed ${max} characters`);
            return false;
        }
        return true;
    }

    // Validate password strength
    public passwordStrength(value: string, field: string): boolean {
        const hasUpperCase = /[A-Z]/.test(value);
        const hasLowerCase = /[a-z]/.test(value);
        const hasNumbers = /\d/.test(value);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(value);

        if (!hasUpperCase) {
            this.addError(field, 'Password must contain at least one uppercase letter');
        }
        if (!hasLowerCase) {
            this.addError(field, 'Password must contain at least one lowercase letter');
        }
        if (!hasNumbers) {
            this.addError(field, 'Password must contain at least one number');
        }
        if (!hasSpecialChar) {
            this.addError(field, 'Password must contain at least one special character');
        }

        return !this.hasErrors();
    }

    // Validate number range
    public numberRange(value: number, min: number, max: number, field: string, message?: string): boolean {
        if (value < min || value > max) {
            this.addError(field, message || `${field} must be between ${min} and ${max}`);
            return false;
        }
        return true;
    }

    // Validate pattern
    public pattern(value: string, pattern: RegExp, field: string, message?: string): boolean {
        if (!pattern.test(value)) {
            this.addError(field, message || `Invalid ${field} format`);
            return false;
        }
        return true;
    }
} 