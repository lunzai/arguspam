import type { Asset } from '$models/asset';
import type { ApiResourceResponse, ApiCollectionResponse } from '$resources/api';

export interface AssetCollection extends ApiCollectionResponse<Asset> {}
export interface AssetResource extends ApiResourceResponse<Asset> {}
