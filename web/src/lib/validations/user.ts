import { z } from 'zod';
import { TIMEZONES } from '$lib/constants/timezones';

export const UserProfileSchema = z.object({
	name: z
		.string()
		.min(2, 'Name must be at least 2 characters')
		.max(100, 'Name must be less than 100 characters'),
	default_timezone: z.enum(TIMEZONES, { message: 'Invalid timezone' }).default('UTC')
});

export const ChangePasswordSchema = z
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

export const ResetPasswordSchema = z
	.object({
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
	status: z.enum(['active', 'inactive']).default('active'),
	default_timezone: z.enum(TIMEZONES, { message: 'Invalid timezone' }).default('UTC')
});

export const UserUpdateRolesSchema = z.object({
	// roleIds: z.array(z.coerce.number().int().positive()).min(1)
	roleIds: z.array(z.string()).min(1, 'Please select at least one role')
});

export type UserProfile = z.infer<typeof UserProfileSchema>;
export type ChangePassword = z.infer<typeof ChangePasswordSchema>;
export type ResetPassword = z.infer<typeof ResetPasswordSchema>;
export type User = z.infer<typeof UserSchema>;
export type UserUpdateRoles = z.infer<typeof UserUpdateRolesSchema>;
