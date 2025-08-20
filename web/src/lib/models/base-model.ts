// Generic relationship types
export interface RelationshipItem<T = Record<string, any>> {
	attributes: T;
}

export type Relationships<T = Record<string, RelationshipItem[]>> = T;

// Base model interface that all models can extend
export interface BaseModel {
	id: number;
	created_at: Date;
	updated_at?: Date;
	relationships?: Relationships;
}
