import type { User } from '../types';

export interface LoginRequestDTO {
    email: string;
    password: string;
}

export interface LoginResponseDTO {
    token: string;
    user: User;
}

export interface RegisterRequestDTO {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
}

export interface ForgotPasswordRequestDTO {
    email: string;
}

export interface ResetPasswordRequestDTO {
    token: string;
    email: string;
    password: string;
    password_confirmation: string;
} 