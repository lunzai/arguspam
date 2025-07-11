import { BaseService } from './base.js';
import type { BaseModel } from '$models/base-model';
import type { OrgCollection } from '$resources/org';
import type { UserResource } from '$lib/resources/user.js';

export class UserService extends BaseService<BaseModel> {
	protected readonly endpoint = '/users';
	protected readonly meEndpoint = '/users/me';

	constructor(token: string, orgId?: number) {
		super('/users', token, orgId);
	}

	async getOrgs(): Promise<OrgCollection> {
		return await this.api.get<OrgCollection>(`${this.meEndpoint}/orgs`);
	}

	async checkOrgAccess(orgId: number): Promise<boolean> {
		await this.api.get<boolean>(`${this.meEndpoint}/orgs/${orgId}`);
		return true;
	}

	async me(): Promise<UserResource> {
		return await this.api.get<UserResource>(`${this.meEndpoint}`);
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
}
