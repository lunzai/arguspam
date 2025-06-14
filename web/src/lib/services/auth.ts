import { BaseService } from './base.js';
import type { BaseModel } from '$models/base-model.js';
import type { LoginResponse } from '$resources/auth';
import type { UserResource } from '$resources/user';

export class AuthService extends BaseService<BaseModel> {
    protected readonly endpoint = '/auth';

	constructor(token: string) {
		super('/auth', token);
	}

	async login(email: string, password: string): Promise<LoginResponse> {
		return await this.api.post<LoginResponse>(`${this.endpoint}/login`, { email, password });
	}

	async logout(): Promise<void> {
		await this.api.post<void>(`${this.endpoint}/logout`);
	}

	async me(): Promise<UserResource> {
		return await this.api.get<UserResource>(`${this.endpoint}/me`);
	}
}