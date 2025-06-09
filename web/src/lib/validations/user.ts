import { z } from 'zod';

export const userProfileSchema = z.object({
	name: z.string().min(2, 'Name must be at least 2 characters').max(100, 'Name must be less than 100 characters')
});

export const userProfileWithEmailSchema = z.object({
	name: z.string().min(2, 'Name must be at least 2 characters').max(100, 'Name must be less than 100 characters'),
	email: z.string().email('Please enter a valid email address').max(100, 'Email must be less than 100 characters')
});

export const createUserSchema = z.object({
	name: z.string().min(2, 'Name must be at least 2 characters').max(100),
	email: z.string().email('Please enter a valid email address').max(100),
	status: z.enum(['active', 'inactive']).default('active'),
	two_factor_enabled: z.boolean().default(false)
});

export const updateUserSchema = createUserSchema.partial().extend({
	id: z.number()
});

export type UserProfile = z.infer<typeof userProfileSchema>;
export type CreateUser = z.infer<typeof createUserSchema>;
export type UpdateUser = z.infer<typeof updateUserSchema>;