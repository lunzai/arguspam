import { PUBLIC_API_URL } from '$env/static/public';
import { PUBLIC_API_REQUEST_TIMEOUT, PUBLIC_ORG_ID_HEADER } from '$env/static/public';
import axios from 'axios';
import https from 'https';
import { dev } from '$app/environment';
import { handleApiError } from '$api/shared.js';

export class ServerApi {
	private baseUrl: string;
	private axiosInstance;
	private currentOrgId: number | null = null;

	constructor(baseUrl?: string) {
		this.baseUrl = baseUrl || PUBLIC_API_URL;
		
		// Create axios instance with SSL handling
		this.axiosInstance = axios.create({
			baseURL: this.baseUrl,
			timeout: Number(PUBLIC_API_REQUEST_TIMEOUT),
			httpsAgent: new https.Agent({
				// Only disable SSL verification in development
				rejectUnauthorized: !dev
			})
		});

		// Add request interceptor to automatically include org context header
		this.axiosInstance.interceptors.request.use((config) => {
			if (this.currentOrgId) {
				config.headers[PUBLIC_ORG_ID_HEADER] = this.currentOrgId.toString();
			}
			return config;
		});
	}

	/**
	 * Set the current organization ID for all subsequent requests
	 */
	setCurrentOrgId(orgId: number | null): void {
		this.currentOrgId = orgId;
	}

	/**
	 * Get the current organization ID
	 */
	getCurrentOrgId(): number | null {
		return this.currentOrgId;
	}

	/**
	 * Make a request to the API with proper error handling
	 */
	async request<T>(
		endpoint: string,
		options: {
			method?: string;
			body?: any;
			headers?: Record<string, string>;
			token?: string;
			orgId?: number;
		} = {}
	): Promise<T> {
		const { method = 'GET', body, headers = {}, token, orgId } = options;

		console.log('API URL:', `${this.baseUrl}${endpoint}`);

		const requestHeaders: Record<string, string> = {
			'Content-Type': 'application/json',
			Accept: 'application/json',
			...headers
		};

		if (token) {
			requestHeaders['Authorization'] = `Bearer ${token}`;
		}

		// Handle temporary org ID override for this request
		if (orgId !== undefined) {
			requestHeaders[PUBLIC_ORG_ID_HEADER] = orgId.toString();
		}

		try {
			const response = await this.axiosInstance({
				url: endpoint,
				method: method.toLowerCase() as any,
				headers: requestHeaders,
				data: body,
			});

			// Handle successful responses with no content (like logout)
			if (response.status === 204 || !response.data) {
				return {} as T;
			}

			return response.data;
		} catch (error) {
			console.log('Error:', error);
			handleApiError(error);
		}
	}

	/**
	 * GET request helper
	 */
	async get<T>(endpoint: string, token?: string, orgId?: number): Promise<T> {
		return this.request<T>(endpoint, { method: 'GET', token, orgId });
	}

	/**
	 * POST request helper
	 */
	async post<T>(endpoint: string, body?: any, token?: string, orgId?: number): Promise<T> {
		return this.request<T>(endpoint, { method: 'POST', body, token, orgId });
	}

	/**
	 * PUT request helper
	 */
	async put<T>(endpoint: string, body?: any, token?: string, orgId?: number): Promise<T> {
		return this.request<T>(endpoint, { method: 'PUT', body, token, orgId });
	}

	/**
	 * DELETE request helper
	 */
	async delete<T>(endpoint: string, token?: string, orgId?: number): Promise<T> {
		return this.request<T>(endpoint, { method: 'DELETE', token, orgId });
	}

	/**
	 * PATCH request helper
	 */
	async patch<T>(endpoint: string, body?: any, token?: string, orgId?: number): Promise<T> {
		return this.request<T>(endpoint, { method: 'PATCH', body, token, orgId });
	}
}

// Default server API client instance
export const serverApi = new ServerApi(); 