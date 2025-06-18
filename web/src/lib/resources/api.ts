/**
 * Laravel API Resource item structure for relationship data
 */
export interface ResourceItem<T> {
	attributes: T;
}

/**
 * Laravel API Resource structure for single items
 */
export interface Resource<T> {
	attributes: T;
	relationships?: Record<string, ResourceItem<any>[]>;
}

/**
 * Laravel API Resource Collection structure for arrays of items
 */
export type Collection<T> = Resource<T>[];

export interface ApiMeta {
	current_page: number;
	from: number;
	last_page: number;
	per_page: number;
	to: number;
	total: number;
}

/**
 * Standard API response wrapper
 */
export interface ApiResponse<T> {
	data: T; // Can be Resource<Model>, Collection<Model>, or raw data
}

/**
 * API response for single Laravel Resource
 */
export interface ApiResourceResponse<T> {
	data: Resource<T>;
}

/**
 * API response for Laravel Resource Collection (with pagination)
 */
export interface ApiCollectionResponse<T> {
	data: Collection<T>;
	meta?: ApiMeta;
}

export interface ApiValidationErrorResponse {
	message: string;
	errors: Record<string, string[]>;
}
