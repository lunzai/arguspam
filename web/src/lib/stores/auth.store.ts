import { writable } from 'svelte/store';
import type { User } from '$lib/api/types';

interface AuthState {
    user: User | null;
    isAuthenticated: boolean;
    isLoading: boolean;
}

function createAuthStore() {
    const { subscribe, set, update } = writable<AuthState>({
        user: null,
        isAuthenticated: false,
        isLoading: true
    });

    return {
        subscribe,
        setUser: (user: User | null) => {
            update(state => ({
                ...state,
                user,
                isAuthenticated: !!user,
                isLoading: false
            }));
        },
        setLoading: (isLoading: boolean) => {
            update(state => ({ ...state, isLoading }));
        },
        reset: () => {
            set({
                user: null,
                isAuthenticated: false,
                isLoading: false
            });
        }
    };
}

export const authStore = createAuthStore(); 