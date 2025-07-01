import { BaseService } from './base.js';
import type { BaseModel } from '$models/base-model';
import type { DashboardResource } from '$resources/dashboard';

export class DashboardService extends BaseService<BaseModel> {
	protected readonly endpoint = '/dashboard';

	constructor(token: string, orgId: number) {
		super('/dashboard', token, orgId);
	}

	async getDashboard(): Promise<DashboardResource> {
		return await this.api.get<DashboardResource>(`${this.endpoint}`);
	}
}