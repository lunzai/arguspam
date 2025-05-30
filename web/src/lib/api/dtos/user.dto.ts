import type { User } from '../types';

export interface CreateUserDTO {
    name: string;
    email: string;
    password: string;
}

export interface UpdateUserDTO {
    name?: string;
    email?: string;
    password?: string;
}

// Response DTOs
export interface UserResponseDTO extends User {
    // Add any additional fields that might be returned from the API
    // but not part of the base User type
} 