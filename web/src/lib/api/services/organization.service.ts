import type { Organization, User } from '../types';
import type { CreateOrganizationDTO, UpdateOrganizationDTO } from '../dtos/organization.dto';
import { BaseService } from './base.service';
import { OrganizationRepository } from '../repositories/organization.repository';
import { OrganizationValidator } from '../validation/organization.validator';
import type { ValidationResult } from '../validation/types';

export class OrganizationService extends BaseService<Organization> {
    protected repository = new OrganizationRepository();
    private validator = new OrganizationValidator();

    // Validate organization creation
    public validateCreate(data: CreateOrganizationDTO): ValidationResult {
        return this.validator.validateCreate(data);
    }

    // Validate organization update
    public validateUpdate(data: UpdateOrganizationDTO): ValidationResult {
        return this.validator.validateUpdate(data);
    }

    // Create organization with validation
    async create(data: CreateOrganizationDTO): Promise<Organization> {
        const validation = this.validateCreate(data);
        if (!validation.isValid) {
            throw new Error(JSON.stringify(validation.errors));
        }
        return super.create(data);
    }

    // Update organization with validation
    async update(id: number, data: UpdateOrganizationDTO): Promise<Organization> {
        const validation = this.validateUpdate(data);
        if (!validation.isValid) {
            throw new Error(JSON.stringify(validation.errors));
        }
        return super.update(id, data);
    }

    // Get organization members
    async getMembers(organizationId: number): Promise<User[]> {
        try {
            const response = await this.repository.getMembers(organizationId);
            return this.transformResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    // Add member to organization
    async addMember(organizationId: number, userId: number): Promise<void> {
        try {
            const response = await this.repository.addMember(organizationId, userId);
            return this.transformResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    // Remove member from organization
    async removeMember(organizationId: number, userId: number): Promise<void> {
        try {
            const response = await this.repository.removeMember(organizationId, userId);
            return this.transformResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    // Get organization assets
    async getAssets(organizationId: number): Promise<any[]> {
        try {
            const response = await this.repository.getAssets(organizationId);
            return this.transformResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    // Business logic methods
    async validateOrganizationName(name: string): Promise<boolean> {
        // Add your organization name validation logic here
        return name.length >= 3 && name.length <= 50;
    }
} 