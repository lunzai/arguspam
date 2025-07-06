import { z } from 'zod';

export const OrgSchema = z.object({
	name: z
		.string()
		.min(2, 'Name must be at least 2 characters')
		.max(100, 'Name must be less than 100 characters'),
	description: z.string().max(255, 'Description must be less than 255 characters').nullish(),
	status: z.enum(['active', 'inactive']).default('active')
});

export type Org = z.infer<typeof OrgSchema>;
