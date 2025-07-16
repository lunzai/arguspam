import { z } from 'zod';

export const loginSchema = z.object({
	email: z
		.string()
		.email('Please enter a valid email address')
		.max(100, 'Email must be less than 100 characters'),
	password: z
		.string()
		.min(8, 'Password must be at least 8 characters')
		.max(100, 'Password must be less than 100 characters')
});

export const forgotPasswordSchema = z.object({
	email: z
		.string()
		.email('Please enter a valid email address')
		.max(100, 'Email must be less than 100 characters')
});

export const twoFactorCodeSchema = z.object({
	code: z
		.string()
		.regex(/^\d{6}$/, 'Code must be 6 digits')
});

export type Login = z.infer<typeof loginSchema>;
export type ForgotPassword = z.infer<typeof forgotPasswordSchema>;
export type TwoFactorCode = z.infer<typeof twoFactorCodeSchema>;