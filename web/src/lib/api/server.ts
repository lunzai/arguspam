import { PUBLIC_API_URL } from '$env/static/public';
import { PUBLIC_API_REQUEST_TIMEOUT, PUBLIC_ORG_ID_HEADER } from '$env/static/public';
import { dev } from '$app/environment';
import axios, { type AxiosInstance } from 'axios';
import https from 'https';
import { error } from '@sveltejs/kit';

export class ServerApi {
	private baseUrl: string;
	private axiosInstance: AxiosInstance;
	private currentOrgId: number | null = null;
	private token: string | null = null;

	constructor(token?: string | null, currentOrgId?: number | null, baseUrl?: string) {
		this.baseUrl = baseUrl || PUBLIC_API_URL;
		this.currentOrgId = currentOrgId || null;
		this.token = token || null;

		// Create axios instance with SSL handling
		this.axiosInstance = axios.create({
			baseURL: this.baseUrl,
			timeout: Number(PUBLIC_API_REQUEST_TIMEOUT),
			httpsAgent: new https.Agent({
				// Only disable SSL verification in development
				// TODO: REMOVE THIS
				// rejectUnauthorized: !dev
				// CORS SSL CSRF
				rejectUnauthorized: false
			})
		});
	}

	private clone(
		overrides: {
			currentOrgId?: number | null;
			token?: string | null;
		} = {}
	): ServerApi {
		return new ServerApi(
			overrides.token !== undefined ? overrides.token : this.token,
			overrides.currentOrgId !== undefined ? overrides.currentOrgId : this.currentOrgId,
			this.baseUrl
		);
	}

	// Builder methods
	withToken(token: string): ServerApi {
		return this.clone({ token });
	}

	withoutToken(): ServerApi {
		return this.clone({ token: null });
	}

	withOrg(currentOrgId: number): ServerApi {
		return this.clone({ currentOrgId });
	}

	withoutOrg(): ServerApi {
		return this.clone({ currentOrgId: null });
	}

	async get<T>(endpoint: string): Promise<T> {
		return this.request<T>(endpoint, { method: 'GET' });
	}

	async post<T>(endpoint: string, body?: any): Promise<T> {
		return this.request<T>(endpoint, { method: 'POST', body });
	}

	async put<T>(endpoint: string, body?: any): Promise<T> {
		return this.request<T>(endpoint, { method: 'PUT', body });
	}

	async delete<T>(endpoint: string, body?: any): Promise<T> {
		return this.request<T>(endpoint, { method: 'DELETE', body });
	}

	async patch<T>(endpoint: string, body?: any): Promise<T> {
		return this.request<T>(endpoint, { method: 'PATCH', body });
	}

	private async request<T>(endpoint: string, options: { method: string; body?: any }): Promise<T> {
		const { method, body } = options;

		const requestHeaders: Record<string, string> = {};

		// Add org ID header if available
		if (this.currentOrgId) {
			requestHeaders[PUBLIC_ORG_ID_HEADER] = this.currentOrgId.toString();
		}

		// Add auth token if available
		if (this.token) {
			requestHeaders['Authorization'] = `Bearer ${this.token}`;
		}
		// console.log(`API ${method}`, endpoint, `orgId: ${this.currentOrgId}`);
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
		} catch (axiosError: any) {
			const status = axiosError.response?.status;
			if (status === 403) {
				throw error(403, 'You are not authorized to view this resource');
			}
			if (status === 404) {
				throw error(404, 'The resource you are looking for does not exist');
			}
			if (status === 500) {
				throw error(500, 'An error occurred while processing your request');
			}
			throw error(
				status || 500,
				axiosError.message || 'An error occurred while processing your request'
			);
		}
	}
}
