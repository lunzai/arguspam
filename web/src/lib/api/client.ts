import { PUBLIC_API_URL, PUBLIC_ORG_ID_HEADER } from '$env/static/public';
import { PUBLIC_API_REQUEST_TIMEOUT } from '$env/static/public';
import axios from 'axios';
import { handleApiError, TokenManager } from '$api/shared.js';
import { orgStore } from '$stores/org.js';
import { get } from 'svelte/store';

export class ClientApi {
	private baseUrl: string;
	private tokenManager = new TokenManager();
	private axiosInstance;
	private isInternalMode = false;

	constructor(baseUrl?: string) {
		this.baseUrl = baseUrl || PUBLIC_API_URL;

		// Create axios instance for backend API calls
		this.axiosInstance = axios.create({
			baseURL: this.baseUrl,
			timeout: Number(PUBLIC_API_REQUEST_TIMEOUT),
			headers: {
				'Content-Type': 'application/json',
				Accept: 'application/json'
			}
		});

		// Add request interceptor to automatically include org context header
		this.axiosInstance.interceptors.request.use((config) => {
			const currentOrgStore = get(orgStore);
			if (currentOrgStore.currentOrgId) {
				config.headers[PUBLIC_ORG_ID_HEADER] = currentOrgStore.currentOrgId.toString();
			}
			return config;
		});
	}

	/**
	 * Get auth token from server-side cookie via SvelteKit endpoint
	 */
	private async fetchAuthToken(): Promise<string | null> {
		const response = await axios.get('/api/auth/token');
		console.log(response);
		return response.data.data.token;
	}

	/**
	 * Clear cached auth token (call when user logs out)
	 */
	clearAuthToken(): void {
		this.tokenManager.clearToken();
	}

	/**
	 * Switch to internal SvelteKit API mode
	 * Returns this for chaining
	 */
	internal(): this {
		this.isInternalMode = true;
		return this;
	}

	/**
	 * Make a request (automatically detects internal vs external mode)
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
		const { method = 'GET', body, headers = {}, requireAuth } = options;

		// Auto-detect auth requirement based on mode
		const shouldRequireAuth = requireAuth ?? !this.isInternalMode;

		if (this.isInternalMode) {
			// Reset internal mode after use
			this.isInternalMode = false;
			return this.makeInternalRequest<T>(endpoint, {
				method,
				body,
				requireAuth: shouldRequireAuth
			});
		} else {
			return this.makeExternalRequest<T>(endpoint, {
				method,
				body,
				headers,
				requireAuth: shouldRequireAuth
			});
		}
	}

	/**
	 * Make a request to internal SvelteKit API routes
	 */
	private async makeInternalRequest<T>(
		endpoint: string,
		options: {
			method?: string;
			body?: any;
			requireAuth?: boolean;
		} = {}
	): Promise<T> {
		const { method = 'GET', body, requireAuth = false } = options;

		console.log('Internal API URL:', endpoint);

		const requestHeaders: Record<string, string> = {
			'Content-Type': 'application/json',
			Accept: 'application/json'
		};

		// Internal routes use cookies, so typically don't need bearer tokens
		if (requireAuth) {
			const token = await this.tokenManager.getToken(() => this.fetchAuthToken());
			if (token) {
				requestHeaders['Authorization'] = `Bearer ${token}`;
			}
		}

		try {
			const response = await axios({
				url: endpoint,
				method: method.toLowerCase() as any,
				headers: requestHeaders,
				data: body
			});

			if (response.status === 204 || !response.data) {
				return {} as T;
			}

			return response.data;
		} catch (error) {
			handleApiError(error);
		}
	}

	/**
	 * Make a request to the external backend API
	 */
	private async makeExternalRequest<T>(
		endpoint: string,
		options: {
			method?: string;
			body?: any;
			headers?: Record<string, string>;
			requireAuth?: boolean;
		} = {}
	): Promise<T> {
		const { method = 'GET', body, headers = {}, requireAuth = true } = options;

		console.log('External API URL:', `${this.baseUrl}${endpoint}`);

		const requestHeaders: Record<string, string> = {
			...headers
		};

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
				data: body
			});

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
	async get<T>(endpoint: string, requireAuth?: boolean): Promise<T> {
		return this.request<T>(endpoint, { method: 'GET', requireAuth });
	}

	/**
	 * POST request helper
	 */
	async post<T>(endpoint: string, body?: any, requireAuth?: boolean): Promise<T> {
		return this.request<T>(endpoint, { method: 'POST', body, requireAuth });
	}

	/**
	 * PUT request helper
	 */
	async put<T>(endpoint: string, body?: any, requireAuth?: boolean): Promise<T> {
		return this.request<T>(endpoint, { method: 'PUT', body, requireAuth });
	}

	/**
	 * DELETE request helper
	 */
	async delete<T>(endpoint: string, requireAuth?: boolean): Promise<T> {
		return this.request<T>(endpoint, { method: 'DELETE', requireAuth });
	}

	/**
	 * PATCH request helper
	 */
	async patch<T>(endpoint: string, body?: any, requireAuth?: boolean): Promise<T> {
		return this.request<T>(endpoint, { method: 'PATCH', body, requireAuth });
	}
}

// Default client API instance
export const clientApi = new ClientApi();
