import { BaseService } from './base.js';
import type { BaseModel } from '$models/base-model.js';
import type { LoginResponse, Login2faResponse } from '$resources/auth';
import type { ApiMeResource } from '$resources/user';

export class AuthService extends BaseService<BaseModel> {
	protected readonly endpoint = '/auth';

	constructor(token: string) {
		super('/auth', token);
	}

	async login(email: string, password: string): Promise<LoginResponse> {
		return await this.api.post<LoginResponse>(`${this.endpoint}/login`, {
			email,
			password
		});
	}

	async verify2fa(code: string, tempKey: string): Promise<Login2faResponse> {
		return await this.api.post<Login2faResponse>(`${this.endpoint}/2fa`, {
			code,
			temp_key: tempKey
		});
	}

	async logout(): Promise<void> {
		await this.api.post<void>(`${this.endpoint}/logout`);
	}

	async me(): Promise<ApiMeResource> {
		return await this.api.get<ApiMeResource>(`${this.endpoint}/me`);
	}
}
