import type { Request } from '$models/request';
import type { ApiResourceResponse, ApiCollectionResponse, Resource, Collection } from '$resources/api';

export interface ApiRequestCollection extends ApiCollectionResponse<Request> {}
export interface ApiRequestResource extends ApiResourceResponse<Request> {}
export interface RequestResource extends Resource<Request> {}
export interface RequestCollection extends Collection<Request> {}
