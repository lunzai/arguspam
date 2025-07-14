import { z } from 'zod';

export const userProfileSchema = z.object({
	name: z
		.string()
		.min(2, 'Name must be at least 2 characters')
		.max(100, 'Name must be less than 100 characters')
});

export const changePasswordSchema = z
	.object({
		currentPassword: z
			.string()
			.min(8, 'Password must be at least 8 characters')
			.max(100, 'Password must be less than 100 characters'),
		newPassword: z
			.string()
			.min(8, 'Password must be at least 8 characters')
			.max(100, 'Password must be less than 100 characters'),
		confirmNewPassword: z
			.string()
			.min(8, 'Password must be at least 8 characters')
			.max(100, 'Password must be less than 100 characters')
	})
	.refine((data) => data.newPassword === data.confirmNewPassword, {
		message: 'Passwords do not match',
		path: ['confirmNewPassword']
	})
	.refine((data) => data.newPassword !== data.currentPassword, {
		message: 'New password cannot be the same as the current password',
		path: ['newPassword']
	});

export const UserSchema = z.object({
	name: z
		.string()
		.min(2, 'Name must be at least 2 characters')
		.max(100, 'Name must be less than 100 characters'),
	email: z
		.string()
		.email('Please enter a valid email address')
		.max(100, 'Email must be less than 100 characters'),
	status: z.enum(['active', 'inactive']).default('active')
});

export const UserUpdateRolesSchema = z.object({
	// roleIds: z.array(z.coerce.number().int().positive()).min(1)
	roleIds: z
		.array(z.string())
		.min(1, 'Please select at least one role')
});

export type UserProfile = z.infer<typeof userProfileSchema>;
export type ChangePassword = z.infer<typeof changePasswordSchema>;
export type User = z.infer<typeof UserSchema>;
export type UserUpdateRoles = z.infer<typeof UserUpdateRolesSchema>;
