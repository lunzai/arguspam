import { PUBLIC_API_URL } from '$env/static/public';
import axios from 'axios';
import { handleApiError, TokenManager } from './shared.js';

export class ClientApiClient {
	private baseUrl: string;
	private tokenManager = new TokenManager();
	private axiosInstance;

	constructor(baseUrl?: string) {
		this.baseUrl = baseUrl || PUBLIC_API_URL;
		
		// Create axios instance for backend API calls
		this.axiosInstance = axios.create({
			baseURL: this.baseUrl,
			timeout: 10000,
			headers: {
				'Content-Type': 'application/json',
				'Accept': 'application/json'
			}
		});
	}

	/**
	 * Get auth token from server-side cookie via SvelteKit endpoint
	 */
	private async fetchAuthToken(): Promise<string | null> {
		const response = await axios.get('/api/auth/token');
		return response.data.token;
	}

	/**
	 * Clear cached auth token (call when user logs out)
	 */
	clearAuthToken(): void {
		this.tokenManager.clearToken();
	}

	/**
	 * Make a request to the backend API directly
	 */
	async request<T>(
		endpoint: string,
		options: {
			method?: string;
			body?: any;
			headers?: Record<string, string>;
			requireAuth?: boolean;
		} = {}
	): Promise<T> {
		const { method = 'GET', body, headers = {}, requireAuth = true } = options;

		console.log('Direct API URL:', `${this.baseUrl}${endpoint}`);

		const requestHeaders: Record<string, string> = {
			...headers
		};

		// Get auth token if required
		if (requireAuth) {
			const token = await this.tokenManager.getToken(() => this.fetchAuthToken());
			if (token) {
				requestHeaders['Authorization'] = `Bearer ${token}`;
			}
		}

		try {
			const response = await this.axiosInstance({
				url: endpoint,
				method: method.toLowerCase() as any,
				headers: requestHeaders,
				data: body,
			});

			// Handle successful responses with no content
			if (response.status === 204 || !response.data) {
				return {} as T;
			}

			return response.data;
		} catch (error) {
			handleApiError(error);
		}
	}

	/**
	 * GET request helper
	 */
	async get<T>(endpoint: string, requireAuth = true): Promise<T> {
		return this.request<T>(endpoint, { method: 'GET', requireAuth });
	}

	/**
	 * POST request helper
	 */
	async post<T>(endpoint: string, body?: any, requireAuth = true): Promise<T> {
		return this.request<T>(endpoint, { method: 'POST', body, requireAuth });
	}

	/**
	 * PUT request helper
	 */
	async put<T>(endpoint: string, body?: any, requireAuth = true): Promise<T> {
		return this.request<T>(endpoint, { method: 'PUT', body, requireAuth });
	}

	/**
	 * DELETE request helper
	 */
	async delete<T>(endpoint: string, requireAuth = true): Promise<T> {
		return this.request<T>(endpoint, { method: 'DELETE', requireAuth });
	}

	/**
	 * PATCH request helper
	 */
	async patch<T>(endpoint: string, body?: any, requireAuth = true): Promise<T> {
		return this.request<T>(endpoint, { method: 'PATCH', body, requireAuth });
	}
}

// Default client API instance
export const clientApi = new ClientApiClient();

// Create a separate instance for SvelteKit API routes (no auth token needed)
export const svelteKitApi = new ClientApiClient(''); // Empty baseURL for relative routes 