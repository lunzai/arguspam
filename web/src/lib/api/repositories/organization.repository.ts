import type { ApiResponse, Organization, User } from '../types';
import { BaseRepository } from './base.repository';

interface CreateOrganizationDTO {
    name: string;
    plan: string;
}

interface UpdateOrganizationDTO {
    name?: string;
    plan?: string;
}

export class OrganizationRepository extends BaseRepository<Organization, CreateOrganizationDTO, UpdateOrganizationDTO> {
    protected endpoint = 'organizations';

    // Get organization members
    async getMembers(organizationId: number): Promise<ApiResponse<User[]>> {
        return this.query<User[]>(`${organizationId}/members`);
    }

    // Add member to organization
    async addMember(organizationId: number, userId: number): Promise<ApiResponse<void>> {
        return this.query<void>(`${organizationId}/members`, {
            method: 'POST',
            data: { user_id: userId }
        });
    }

    // Remove member from organization
    async removeMember(organizationId: number, userId: number): Promise<ApiResponse<void>> {
        return this.query<void>(`${organizationId}/members/${userId}`, {
            method: 'DELETE'
        });
    }

    // Get organization assets
    async getAssets(organizationId: number): Promise<ApiResponse<any[]>> {
        return this.query<any[]>(`${organizationId}/assets`);
    }
} 