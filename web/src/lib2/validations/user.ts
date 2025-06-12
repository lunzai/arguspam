import { z } from 'zod';

export const userProfileSchema = z.object({
	name: z.string()
		.min(2, 'Name must be at least 2 characters')
		.max(100, 'Name must be less than 100 characters')
});

export const changePasswordSchema = z.object({
	currentPassword: z.string()
		.min(8, 'Password must be at least 8 characters')
		.max(100, 'Password must be less than 100 characters'),
	newPassword: z.string()
		.min(8, 'Password must be at least 8 characters')
		.max(100, 'Password must be less than 100 characters'),
	confirmNewPassword: z.string()
		.min(8, 'Password must be at least 8 characters')
		.max(100, 'Password must be less than 100 characters')
}).refine((data) => data.newPassword === data.confirmNewPassword, {
	message: 'Passwords do not match',
	path: ['confirmNewPassword']
});

export const UserSchema = z.object({
	name: z.string().min(2, 'Name must be at least 2 characters').max(100),
	email: z.string().email('Please enter a valid email address').max(100),
	status: z.enum(['active', 'inactive']).default('active'),
});

export type UserProfile = z.infer<typeof userProfileSchema>;
export type ChangePassword = z.infer<typeof changePasswordSchema>;
export type User = z.infer<typeof UserSchema>;