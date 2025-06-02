import axios, { type AxiosInstance, type AxiosRequestConfig } from 'axios';
import { API_URL, AUTH_TOKEN_KEY } from '$env/static/private';
import type { RequestEvent } from '@sveltejs/kit';

// Create axios instance for backend API
const backendApi: AxiosInstance = axios.create({
	baseURL: API_URL,
	timeout: 30000,
	headers: {
		'Content-Type': 'application/json',
		'Accept': 'application/json'
	}
});

// Get auth token from cookies
function getAuthToken(event: RequestEvent): string | null {
	return event.cookies.get(AUTH_TOKEN_KEY) || null;
}

// Create authenticated request config
function createAuthConfig(event: RequestEvent, config?: AxiosRequestConfig): AxiosRequestConfig {
	const token = getAuthToken(event);
	const authConfig: AxiosRequestConfig = {
		...config,
		headers: {
			...config?.headers,
		}
	};

	if (token) {
		authConfig.headers = {
			...authConfig.headers,
			'Authorization': `Bearer ${token}`
		};
	}

	return authConfig;
}

// Server-side API service
export const serverApi = {
	// GET request
	get: async <T = any>(event: RequestEvent, url: string, config?: AxiosRequestConfig) => {
		const authConfig = createAuthConfig(event, config);
		return backendApi.get<T>(url, authConfig);
	},

	// POST request
	post: async <T = any>(event: RequestEvent, url: string, data?: any, config?: AxiosRequestConfig) => {
		const authConfig = createAuthConfig(event, config);
		return backendApi.post<T>(url, data, authConfig);
	},

	// PUT request
	put: async <T = any>(event: RequestEvent, url: string, data?: any, config?: AxiosRequestConfig) => {
		const authConfig = createAuthConfig(event, config);
		return backendApi.put<T>(url, data, authConfig);
	},

	// PATCH request
	patch: async <T = any>(event: RequestEvent, url: string, data?: any, config?: AxiosRequestConfig) => {
		const authConfig = createAuthConfig(event, config);
		return backendApi.patch<T>(url, data, authConfig);
	},

	// DELETE request
	delete: async <T = any>(event: RequestEvent, url: string, config?: AxiosRequestConfig) => {
		const authConfig = createAuthConfig(event, config);
		return backendApi.delete<T>(url, authConfig);
	},

	// Raw axios instance for special cases
	axios: backendApi
};

export default serverApi; 