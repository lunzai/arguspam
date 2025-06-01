import { writable, derived } from 'svelte/store';
import { AuthService } from '$lib/api/services/auth.service';
import { UserService } from '$lib/api/services/user.service';
import type { User } from '$lib/api/types';
import { browser } from '$app/environment';

const authService = new AuthService();
const userService = new UserService();

// Create stores for user data and loading state
const user = writable<User | null>(null);
const isLoading = writable<boolean>(true);

// Create derived stores
export const isAuthenticated = derived(user, ($user) => $user !== null);
export const authLoading = derived(isLoading, ($isLoading) => $isLoading);

// Initialize the store
async function initialize() {
    if (!browser) {
        isLoading.set(false);
        return;
    }
    
    try {
        const currentUser = await userService.getCurrentUser();
        user.set(currentUser);
    } catch (error: any) {
        console.error('Failed to initialize auth store:', error);
        user.set(null);
    } finally {
        isLoading.set(false);
    }
}

// Logout function
async function logout() {
    try {
        // Call API to logout (invalidate token on server)
        await authService.logout();
    } catch (error) {
        console.error('API logout error:', error);
        // Continue with client-side cleanup even if API call fails
    }
    
    // Clear client-side state
    user.set(null);
    
    // Redirect to logout endpoint which will clear cookies
    if (browser) {
        window.location.href = '/auth/logout';
    }
}

// Function to set user data (for use after successful login)
function setUser(userData: User) {
    user.set(userData);
}

// Initialize the store only in browser environment
if (browser) {
    initialize();
}

export const auth = {
    subscribe: user.subscribe,
    logout,
    setUser,
    isAuthenticated,
    authLoading,
}; 