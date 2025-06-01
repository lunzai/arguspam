import axios from 'axios';
import type { AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios';
import { browser } from '$app/environment';

class ApiClient {
    private client: AxiosInstance;

    constructor(private isServerSide: boolean = false) {
        this.client = axios.create({
            baseURL: this.isServerSide ? process.env.VITE_API_URL : import.meta.env.VITE_API_URL,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        });

        if (!this.isServerSide) {
            this.setupClientInterceptors();
        }
    }

    private async getAuthToken(): Promise<string | null> {
        if (!browser || this.isServerSide) return null;
        
        try {
            const response = await fetch('/api/auth/token', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                return data.token;
            }
            
            return null;
        } catch (error) {
            return null;
        }
    }

    private setupClientInterceptors() {
        // Request interceptor for client-side only
        this.client.interceptors.request.use(
            async (axiosConfig) => {
                if (browser) {
                    const token = await this.getAuthToken();
                    if (token) {
                        axiosConfig.headers.Authorization = `Bearer ${token}`;
                    }
                }
                return axiosConfig;
            },
            (error) => {
                return Promise.reject(error);
            }
        );

        // Response interceptor
        this.client.interceptors.response.use(
            (response) => response,
            async (error) => {
                // Let the components handle the error responses
                return Promise.reject(error);
            }
        );
    }

    // Set authorization header for server-side requests
    public setAuthHeader(token: string) {
        this.client.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    }

    public async get<T>(url: string, config?: AxiosRequestConfig): Promise<T> {
        const response: AxiosResponse<T> = await this.client.get(url, config);
        return response.data;
    }

    public async post<T>(url: string, data?: any, config?: AxiosRequestConfig): Promise<T> {
        const response: AxiosResponse<T> = await this.client.post(url, data, config);
        return response.data;
    }

    public async put<T>(url: string, data?: any, config?: AxiosRequestConfig): Promise<T> {
        const response: AxiosResponse<T> = await this.client.put(url, data, config);
        return response.data;
    }

    public async delete<T>(url: string, config?: AxiosRequestConfig): Promise<T> {
        const response: AxiosResponse<T> = await this.client.delete(url, config);
        return response.data;
    }
}

// Client-side API client
export const apiClient = new ApiClient();

// Factory function for server-side API client
export const createServerApiClient = (authToken?: string) => {
    const client = new ApiClient(true);
    if (authToken) {
        client.setAuthHeader(authToken);
    }
    return client;
}; 