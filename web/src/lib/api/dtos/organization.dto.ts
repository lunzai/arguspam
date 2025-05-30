import type { Organization } from '../types';

export interface CreateOrganizationDTO {
    name: string;
    plan: string;
}

export interface UpdateOrganizationDTO {
    name?: string;
    plan?: string;
}

// Response DTOs
export interface OrganizationResponseDTO extends Organization {
    // Add any additional fields that might be returned from the API
    // but not part of the base Organization type
}

// Member management DTOs
export interface AddMemberDTO {
    user_id: number;
}

export interface RemoveMemberDTO {
    user_id: number;
} 