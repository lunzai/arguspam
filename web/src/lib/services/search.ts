import { BaseService } from './base.js';
import type { BaseModel } from '$models/base-model';

export class SearchService extends BaseService<BaseModel> {
	constructor(endpoint: string, token: string, currentOrgId: number) {
		super(endpoint, token, currentOrgId);
	}
}