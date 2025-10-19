import { BaseService } from './base.js';
import type { Request } from '$models/request';
import type {
	ApiRequestResource,
	RequestPermission,
	RequestPermissionResource
} from '$lib/resources/request';

export class RequestService extends BaseService<Request> {
	protected readonly endpoint = '/requests';

	constructor(token: string, orgId?: number) {
		super('/requests', token, orgId);
	}

	async permission(id: number): Promise<RequestPermission> {
		try {
			const response = await this.api.get<RequestPermissionResource>(
				`${this.endpoint}/${id}/permissions`
			);
			return response.data;
		} catch (error) {
			return {
				canApprove: false,
				canCancel: false
			};
		}
	}

	async approve(id: number, data: any): Promise<ApiRequestResource> {
		return await this.api.post(`${this.endpoint}/${id}/approve`, data);
	}

	async reject(id: number, data: any): Promise<ApiRequestResource> {
		return await this.api.put(`${this.endpoint}/${id}/reject`, data);
	}

	async cancel(id: number): Promise<ApiRequestResource> {
		return await this.api.delete(`${this.endpoint}/${id}/cancel`);
	}
}
