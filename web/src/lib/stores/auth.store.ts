import { writable, derived } from 'svelte/store';
import { AuthService } from '$lib/api/services/auth.service';
import type { User } from '$lib/api/types';
import { browser } from '$app/environment';

const authService = new AuthService();

// Create a writable store for the user
const user = writable<User | null>(null);

// Create a derived store for authentication status
export const isAuthenticated = derived(user, ($user) => $user !== null);

// Initialize the store
async function initialize() {
    if (!browser) return;
    
    try {
        const currentUser = await authService.getCurrentUser();
        user.set(currentUser);
    } catch (error) {
        console.error('Failed to initialize auth store:', error);
        user.set(null);
    }
}

// Login function
async function login(email: string, password: string) {
    try {
        const response = await authService.login({ email, password });
        // Ensure the user object has all required fields
        const userData: User = {
            id: response.user.id,
            name: response.user.name,
            email: response.user.email,
            created_at: new Date().toISOString(), // These should come from the API
            updated_at: new Date().toISOString(), // These should come from the API
        };
        user.set(userData);
        return response;
    } catch (error) {
        user.set(null);
        throw error;
    }
}

// Logout function
async function logout() {
    try {
        await authService.logout();
        user.set(null);
    } catch (error) {
        console.error('Logout error:', error);
        // Still clear the user state even if the API call fails
        user.set(null);
        throw error;
    }
}

// Initialize the store only in browser environment
if (browser) {
    initialize();
}

export const auth = {
    subscribe: user.subscribe,
    login,
    logout,
    isAuthenticated,
}; 