import { z } from 'zod';

export const LoginSchema = z.object({
	email: z
		.string()
		.email('Please enter a valid email address')
		.max(100, 'Email must be less than 100 characters'),
	password: z
		.string()
		.min(8, 'Password must be at least 8 characters')
		.max(100, 'Password must be less than 100 characters')
});

export const ForgotPasswordSchema = z.object({
	email: z
		.string()
		.email('Please enter a valid email address')
		.max(100, 'Email must be less than 100 characters')
});

export const TwoFactorCodeSchema = z.object({
	code: z.string().regex(/^\d{6}$/, 'Code must be 6 digits')
});

export const TwoFactorVerifySchema = z.object({
	code: z.string().regex(/^\d{6}$/, 'Code must be 6 digits'),
	temp_key: z.string().regex(/^[a-zA-Z0-9]{32}$/, 'Invalid temp key')
});

export type Login = z.infer<typeof LoginSchema>;
export type ForgotPassword = z.infer<typeof ForgotPasswordSchema>;
export type TwoFactorCode = z.infer<typeof TwoFactorCodeSchema>;
export type TwoFactorVerify = z.infer<typeof TwoFactorVerifySchema>;
