import type { ApiError } from '$lib/shared/types/error.js';
import { API_URL } from '$env/static/private';
import axios, { type AxiosError } from 'axios';
import https from 'https';
import { dev } from '$app/environment';

export class ApiClient {
	private baseUrl: string;
	private axiosInstance;

	constructor(baseUrl?: string) {
		this.baseUrl = baseUrl || API_URL;
		
		// Create axios instance with SSL handling
		this.axiosInstance = axios.create({
			baseURL: this.baseUrl,
			timeout: 10000,
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
			
			if (axios.isAxiosError(error)) {
				const axiosError = error as AxiosError;
				
				if (axiosError.response) {
					// Server responded with error status
					const errorData = axiosError.response.data as any;
					const apiError: ApiError = {
						message: errorData?.message || 'An error occurred',
						status: axiosError.response.status,
						errors: errorData?.errors || {}
					};
					throw apiError;
				} else if (axiosError.request) {
					// Network error
					throw {
						message: 'Network error. Please check your connection.',
						status: 0
					} as ApiError;
				}
			}
			
			// Unknown error
			throw {
				message: 'An unexpected error occurred',
				status: 0
			} as ApiError;
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

// Default API client instance
export const apiClient = new ApiClient();
