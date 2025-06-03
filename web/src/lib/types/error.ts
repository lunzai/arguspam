export interface ValidationError {
	message: string;
	errors: Record<string, string[]>;
}

export interface ApiError {
	message: string;
	status: number;
	errors?: Record<string, string[]>;
} 