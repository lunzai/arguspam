import type {
	ApiCollectionResponse,
	ApiResourceResponse,
	Resource,
	Collection
} from '$resources/api';
import type { Org } from '$models/org';

export interface ApiOrgCollection extends ApiCollectionResponse<Org> {}
export interface ApiOrgResource extends ApiResourceResponse<Org> {}
export interface OrgResource extends Resource<Org> {}
export interface OrgCollection extends Collection<Org> {}
