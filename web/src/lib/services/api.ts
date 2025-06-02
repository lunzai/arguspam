import axios, { type AxiosInstance, type AxiosRequestConfig } from 'axios';
import { browser } from '$app/environment';

// Create axios instance for SvelteKit API routes
const api: AxiosInstance = axios.create({
	baseURL: '/', // SvelteKit API routes
	timeout: 30000,
	headers: {
		'Content-Type': 'application/json',
		'Accept': 'application/json'
	}
});

// Add response interceptor to handle auth errors
api.interceptors.response.use(
	(response) => response,
	(error) => {
		if (error.response?.status === 401) {
			// Token expired or invalid, redirect to login
			if (browser) {
				window.location.href = '/auth/login';
			}
		}
		return Promise.reject(error);
	}
);

// API service functions - now calling SvelteKit routes
export const apiService = {
	// GET request
	get: <T = any>(url: string, config?: AxiosRequestConfig) => 
		api.get<T>(`/api${url}`, config),
	
	// POST request
	post: <T = any>(url: string, data?: any, config?: AxiosRequestConfig) => 
		api.post<T>(`/api${url}`, data, config),
	
	// PUT request
	put: <T = any>(url: string, data?: any, config?: AxiosRequestConfig) => 
		api.put<T>(`/api${url}`, data, config),
	
	// PATCH request
	patch: <T = any>(url: string, data?: any, config?: AxiosRequestConfig) => 
		api.patch<T>(`/api${url}`, data, config),
	
	// DELETE request
	delete: <T = any>(url: string, config?: AxiosRequestConfig) => 
		api.delete<T>(`/api${url}`, config),
	
	// Get current user
	getCurrentUser: () => 
		api.get('/api/users/me'),
	
	// Check if token is valid (now server-side)
	validateToken: async (): Promise<boolean> => {
		try {
			await api.get('/api/users/me');
			return true;
		} catch {
			return false;
		}
	}
};

export default apiService; 