import { z } from 'zod';

export const RoleSchema = z.object({
	name: z
		.string()
		.min(2, 'Name must be at least 2 characters')
		.max(100, 'Name must be less than 100 characters'),
	description: z
		.string()
		.max(255, 'Description must be less than 255 characters')
		.nullish(),
	is_default: z
        .boolean()
        .default(false)
});

export type Role = z.infer<typeof RoleSchema>;