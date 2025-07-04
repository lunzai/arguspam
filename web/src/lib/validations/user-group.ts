import { z } from 'zod';

export const UserGroupSchema = z.object({
	org_id: z.number().int(),
	name: z
		.string()
		.min(2, 'Name must be at least 2 characters')
		.max(100, 'Name must be less than 100 characters')
		.optional()
		.nullable(),
	description: z
		.string()
		.max(255, 'Description must be less than 255 characters')
		.nullish()
		.optional()
		.nullable(),
	status: z.enum(['active', 'inactive']).default('active')
});

export type UserGroup = z.infer<typeof UserGroupSchema>;
