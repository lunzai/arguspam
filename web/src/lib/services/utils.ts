import { BaseService, type BaseFilterParams } from './base.js';
import type { BaseModel } from '$models/base-model';
import type { TimezoneCollection } from '$lib/resources/utils.js';

export class UserService extends BaseService<BaseModel> {
	protected readonly endpoint = '/users';
	protected readonly meEndpoint = '/users/me';

	constructor(token: string = '') {
		super('/utils', token);
	}

	async getList(): Promise<TimezoneCollection> {
		return await this.api.get<TimezoneCollection>(`${this.endpoint}/timezones`);
	}
}
