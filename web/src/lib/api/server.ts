import { PUBLIC_API_URL } from '$env/static/public';
import { PUBLIC_API_REQUEST_TIMEOUT } from '$env/static/public';
import axios from 'axios';
import https from 'https';
import { dev } from '$app/environment';
import { handleApiError } from '$api/shared.js';

export class ServerApi {
	private baseUrl: string;
	private axiosInstance;

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
		} = {}
	): Promise<T> {
		const { method = 'GET', body, headers = {}, token } = options;

		console.log('API URL:', `${this.baseUrl}${endpoint}`);

		const requestHeaders: Record<string, string> = {
			'Content-Type': 'application/json',
			Accept: 'application/json',
			...headers
		};

		if (token) {
			requestHeaders['Authorization'] = `Bearer ${token}`;
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
	async get<T>(endpoint: string, token?: string): Promise<T> {
		return this.request<T>(endpoint, { method: 'GET', token });
	}

	/**
	 * POST request helper
	 */
	async post<T>(endpoint: string, body?: any, token?: string): Promise<T> {
		return this.request<T>(endpoint, { method: 'POST', body, token });
	}

	/**
	 * PUT request helper
	 */
	async put<T>(endpoint: string, body?: any, token?: string): Promise<T> {
		return this.request<T>(endpoint, { method: 'PUT', body, token });
	}

	/**
	 * DELETE request helper
	 */
	async delete<T>(endpoint: string, token?: string): Promise<T> {
		return this.request<T>(endpoint, { method: 'DELETE', token });
	}

	/**
	 * PATCH request helper
	 */
	async patch<T>(endpoint: string, body?: any, token?: string): Promise<T> {
		return this.request<T>(endpoint, { method: 'PATCH', body, token });
	}
}

// Default server API client instance
export const serverApi = new ServerApi(); 