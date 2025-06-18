import type { ApiCollectionResponse, ApiResourceResponse } from '$resources/api';
import type { Org } from '$models/org';

export interface OrgCollection extends ApiCollectionResponse<Org> {}
export interface OrgResource extends ApiResourceResponse<Org> {}
