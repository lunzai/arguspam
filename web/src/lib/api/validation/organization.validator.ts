import { Validator } from './validator';
import type { ValidationResult } from './types';
import type { CreateOrganizationDTO, UpdateOrganizationDTO } from '../dtos/organization.dto';

export class OrganizationValidator extends Validator {
    // Validate organization creation
    public validateCreate(data: CreateOrganizationDTO): ValidationResult {
        this.clearErrors();

        // Required fields
        this.required(data.name, 'name');
        this.required(data.plan, 'plan');

        // Name validation
        if (data.name) {
            this.minLength(data.name, 3, 'name');
            this.maxLength(data.name, 50, 'name');
            this.pattern(data.name, /^[a-zA-Z0-9\s-]+$/, 'name', 'Organization name can only contain letters, numbers, spaces, and hyphens');
        }

        // Plan validation
        if (data.plan) {
            this.pattern(data.plan, /^(basic|pro|enterprise)$/, 'plan', 'Invalid plan type');
        }

        return {
            isValid: !this.hasErrors(),
            errors: this.getErrors()
        };
    }

    // Validate organization update
    public validateUpdate(data: UpdateOrganizationDTO): ValidationResult {
        this.clearErrors();

        // Name validation (if provided)
        if (data.name) {
            this.minLength(data.name, 3, 'name');
            this.maxLength(data.name, 50, 'name');
            this.pattern(data.name, /^[a-zA-Z0-9\s-]+$/, 'name', 'Organization name can only contain letters, numbers, spaces, and hyphens');
        }

        // Plan validation (if provided)
        if (data.plan) {
            this.pattern(data.plan, /^(basic|pro|enterprise)$/, 'plan', 'Invalid plan type');
        }

        return {
            isValid: !this.hasErrors(),
            errors: this.getErrors()
        };
    }
} 