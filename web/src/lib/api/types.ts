export interface ApiResponse<T = any> {
    data: T;
    message?: string;
    status: number;
}

export interface PaginatedResponse<T> extends ApiResponse {
    data: {
        data: T[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    two_factor_enabled: boolean;
    two_factor_confirmed_at: string | null;
    status: string;
    last_login_at: string | null;
    created_by: number | null;
    created_at: string;
    updated_by: number | null;
    updated_at: string;
}

export interface Organization {
    id: number;
    name: string;
    plan: string;
    created_at: string;
    updated_at: string;
}

export interface Asset {
    id: number;
    name: string;
    type: string;
    created_at: string;
    updated_at: string;
}

export interface Session {
    id: number;
    user_id: number;
    asset_id: number;
    started_at: string;
    ended_at: string | null;
    status: 'active' | 'ended';
}

export interface Role {
    id: number;
    name: string;
    description: string;
    created_at: string;
    updated_at: string;
}

export interface Permission {
    id: number;
    name: string;
    description: string;
    created_at: string;
    updated_at: string;
}