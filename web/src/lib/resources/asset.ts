import type { Asset } from '$models/asset';
import type { ApiResourceResponse, ApiCollectionResponse, Resource, Collection } from '$resources/api';

export interface ApiAssetCollection extends ApiCollectionResponse<Asset> {}
export interface ApiAssetResource extends ApiResourceResponse<Asset> {}
export interface AssetResource extends Resource<Asset> {}
export interface AssetCollection extends Collection<Asset> {}
