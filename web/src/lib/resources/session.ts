import type { Session } from '$models/session';
import type { ApiResourceResponse, ApiCollectionResponse, Resource, Collection } from '$resources/api';

export interface ApiSessionCollection extends ApiCollectionResponse<Session> {}
export interface ApiSessionResource extends ApiResourceResponse<Session> {}
export interface SessionResource extends Resource<Session> {}
export interface SessionCollection extends Collection<Session> {}
