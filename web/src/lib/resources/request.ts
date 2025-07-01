import type { Request } from '$models/request';
import type { ApiResourceResponse, ApiCollectionResponse } from '$resources/api';

export interface RequestCollection extends ApiCollectionResponse<Request> {}
export interface RequestResource extends ApiResourceResponse<Request> {}
