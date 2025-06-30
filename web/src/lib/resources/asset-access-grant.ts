import type { AssetAccessGrant } from '$models/asset-access-grant';
import type { ApiResourceResponse, ApiCollectionResponse } from '$resources/api';

export interface AssetAccessGrantCollection extends ApiCollectionResponse<AssetAccessGrant> {}
export interface AssetAccessGrantResource extends ApiResourceResponse<AssetAccessGrant> {}
