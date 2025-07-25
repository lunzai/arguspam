import type { AssetAccount } from '$models/asset-account';
import type { ApiResourceResponse, ApiCollectionResponse, Resource, Collection } from '$resources/api';

export interface ApiAssetAccountCollection extends ApiCollectionResponse<AssetAccount> {}
export interface ApiAssetAccountResource extends ApiResourceResponse<AssetAccount> {}
export interface AssetAccountResource extends Resource<AssetAccount> {}
export interface AssetAccountCollection extends Collection<AssetAccount> {}