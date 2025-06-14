import { BaseService } from './base.js';
import type { BaseModel } from '$models/base-model';
import type { OrgCollection } from '$resources/org';
import type { UserResource } from '$lib/resources/user.js';

export class UserService extends BaseService<BaseModel> {
    protected readonly endpoint = '/users';

	constructor(token: string) {
		super('/users', token);
	}

	async getOrgs(): Promise<OrgCollection> {
		return await this.api.get<OrgCollection>(`${this.endpoint}/me/orgs`);
	}

	async checkOrgAccess(orgId: number): Promise<boolean> {
		await this.api.get<boolean>(`${this.endpoint}/me/orgs/${orgId}`);
		return true;
	}

	async me(): Promise<UserResource> {
		return await this.api.get<UserResource>(`${this.endpoint}/me`);
	}
}