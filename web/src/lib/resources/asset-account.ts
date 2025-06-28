import type { AssetAccount } from '$models/asset-account';
import type { ApiResourceResponse, ApiCollectionResponse } from '$resources/api';

export interface AssetAccountCollection extends ApiCollectionResponse<AssetAccount> {}
export interface AssetAccountResource extends ApiResourceResponse<AssetAccount> {} 