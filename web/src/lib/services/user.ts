import { BaseService } from './base.js';
import type { BaseModel } from '$models/base-model';
import type { ApiOrgCollection } from '$resources/org';
import type { ApiUserResource, UserResource } from '$lib/resources/user.js';
import type { TwoFactorQrCodeResponse } from '$resources/user';

export class UserService extends BaseService<BaseModel> {
	protected readonly endpoint = '/users';
	protected readonly meEndpoint = '/users/me';

	constructor(token: string, orgId?: number) {
		super('/users', token, orgId);
	}

	async getOrgs(): Promise<ApiOrgCollection> {
		return await this.api.get<ApiOrgCollection>(`${this.meEndpoint}/orgs`);
	}

	async checkOrgAccess(orgId: number): Promise<boolean> {
		await this.api.get<boolean>(`${this.meEndpoint}/orgs/${orgId}`);
		return true;
	}

	async me(): Promise<ApiUserResource> {
		return await this.api.get<ApiUserResource>(`${this.meEndpoint}`);
	}

	async changePassword(
		currentPassword: string,
		newPassword: string,
		confirmNewPassword: string
	): Promise<void> {
		return await this.api.put<void>(`${this.meEndpoint}/change-password`, {
			current_password: currentPassword,
			new_password: newPassword,
			new_password_confirmation: confirmNewPassword
		});
	}

	async resetPassword(id: number, newPassword: string, confirmNewPassword: string): Promise<void> {
		return await this.api.post<void>(`${this.endpoint}/${id}/reset-password`, {
			new_password: newPassword,
			new_password_confirmation: confirmNewPassword
		});
	}

	async updateRoles(id: number, roleIds: number[]): Promise<void> {
		return await this.api.post<void>(`${this.endpoint}/${id}/roles`, {
			role_ids: roleIds
		});
	}

	async updateTwoFactor(id: number, enabled: boolean): Promise<void> {
		return enabled ? await this.enableTwoFactor(id) : await this.disableTwoFactor(id);
	}

	async enableTwoFactor(id: number): Promise<void> {
		return await this.api.post<void>(`${this.endpoint}/${id}/2fa`);
	}

	async disableTwoFactor(id: number): Promise<void> {
		return await this.api.delete<void>(`${this.endpoint}/${id}/2fa`);
	}

	async getTwoFactorQrCode(id: number): Promise<TwoFactorQrCodeResponse> {
		return await this.api.get<TwoFactorQrCodeResponse>(`${this.endpoint}/${id}/2fa`);
	}

	async verifyTwoFactor(id: number, code: string): Promise<UserResource> {
		return await this.api.put<UserResource>(`${this.endpoint}/${id}/2fa`, {
			code: code
		});
	}
}
