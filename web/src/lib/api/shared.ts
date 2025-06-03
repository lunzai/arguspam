import type { ApiError } from '$lib/types/error.js';
import axios, { type AxiosError } from 'axios';

/**
 * Handle axios errors and convert to ApiError format
 */
export function handleApiError(error: unknown): never {
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
				status: 0,
				errors: {}
			} as ApiError;
		}
	}
	
	// Unknown error
	throw {
		message: 'An unexpected error occurred',
		status: 0,
		errors: {}
	} as ApiError;
}

/**
 * Token management utilities
 */
export interface TokenInfo {
	token: string;
	expiresAt?: number;
}

export class TokenManager {
	private tokenInfo: TokenInfo | null = null;
	private tokenPromise: Promise<string | null> | null = null;

	/**
	 * Check if token is expired
	 */
	private isTokenExpired(): boolean {
		if (!this.tokenInfo?.expiresAt) return false;
		return Date.now() >= this.tokenInfo.expiresAt;
	}

	/**
	 * Get token with automatic refresh if expired
	 */
	async getToken(fetchTokenFn: () => Promise<string | null>): Promise<string | null> {
		// If we have a valid cached token, return it
		if (this.tokenInfo && !this.isTokenExpired()) {
			return this.tokenInfo.token;
		}

		// Prevent multiple simultaneous token requests
		if (this.tokenPromise) {
			return this.tokenPromise;
		}

		this.tokenPromise = this.fetchAndCacheToken(fetchTokenFn);
		const token = await this.tokenPromise;
		this.tokenPromise = null;
		
		return token;
	}

	private async fetchAndCacheToken(fetchTokenFn: () => Promise<string | null>): Promise<string | null> {
		try {
			const token = await fetchTokenFn();
			if (token) {
				// Cache token with a reasonable expiration time (e.g., 1 hour from now)
				this.tokenInfo = {
					token,
					expiresAt: Date.now() + (60 * 60 * 1000) // 1 hour
				};
			} else {
				this.tokenInfo = null;
			}
			return token;
		} catch (error) {
			console.warn('Failed to fetch auth token:', error);
			this.tokenInfo = null;
			return null;
		}
	}

	/**
	 * Clear cached token
	 */
	clearToken(): void {
		this.tokenInfo = null;
		this.tokenPromise = null;
	}
} 