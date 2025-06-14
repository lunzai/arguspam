import { BaseService } from './base.js';
import type { BaseModel } from '$models/base-model';
import type { OrgCollection } from '$resources/org';


export class OrgService extends BaseService<BaseModel> {
    protected readonly endpoint = '/orgs';

	constructor(token: string) {
		super('/orgs', token);
	}

	
}