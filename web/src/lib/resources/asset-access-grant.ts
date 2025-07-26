import type { AssetAccessGrant } from '$models/asset-access-grant';
import type {
	ApiResourceResponse,
	ApiCollectionResponse,
	Resource,
	Collection
} from '$resources/api';

export interface ApiAssetAccessGrantCollection extends ApiCollectionResponse<AssetAccessGrant> {}
export interface ApiAssetAccessGrantResource extends ApiResourceResponse<AssetAccessGrant> {}
export interface AssetAccessGrantResource extends Resource<AssetAccessGrant> {}
export interface AssetAccessGrantCollection extends Collection<AssetAccessGrant> {}
