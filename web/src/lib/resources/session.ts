import type { Session } from '$models/session';
import type { ApiResourceResponse, ApiCollectionResponse } from '$resources/api';

export interface SessionCollection extends ApiCollectionResponse<Session> {}
export interface SessionResource extends ApiResourceResponse<Session> {} 