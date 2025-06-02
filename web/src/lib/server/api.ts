import type { ApiError } from '$lib/types/auth.js';
import { API_URL } from '$env/static/private';
import axios, { type AxiosError } from 'axios';
import https from 'https';
import { dev } from '$app/environment';

export class ApiClient {
	private baseUrl: string;
	private axiosInstance;

	constructor() {
		this.baseUrl = API_URL;
		
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
	 * Make a request to the Laravel API with proper error handling
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
	 * Login to Laravel API
	 */
	async login(email: string, password: string) {
		return this.request('/auth/login', {
			method: 'POST',
			body: { email, password }
		});
	}

	/**
	 * Get current user from Laravel API
	 */
	async me(token: string) {
		return this.request('/auth/me', {
			token
		});
	}

	/**
	 * Logout from Laravel API
	 */
	async logout(token: string) {
		return this.request('/auth/logout', {
			method: 'POST',
			token
		});
	}
}

export const apiClient = new ApiClient();
