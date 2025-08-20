import { z } from 'zod';

const isValidIP = (host: string): boolean => {
	const parts = host.split('.');
	if (parts.length !== 4) {
		return false;
	}
	return parts.every((part) => {
		const num = parseInt(part, 10);
		return !isNaN(num) && num >= 0 && num <= 255 && part === num.toString();
	});
};

const isValidHostname = (host: string): boolean => {
	if (host === 'localhost') {
		return true;
	}
	const segments = host.split('.');
	if (segments.length === 4 && segments.every((seg) => /^\d+$/.test(seg))) {
		return false; // This looks like an IP, so it must be a valid IP or nothing
	}
	const hostnameRegex =
		/^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
	return hostnameRegex.test(host);
};

export const AssetSchema = z.object({
	org_id: z.number().int(),
	name: z
		.string()
		.min(2, 'Name must be at least 2 characters')
		.max(100, 'Name must be less than 100 characters'),
	description: z.string().max(255, 'Description must be less than 255 characters').nullish(),
	status: z.enum(['active', 'inactive']).default('active'),
	host: z
		.string()
		.min(1, 'Host is required')
		.max(255, 'Host must be less than 255 characters')
		.refine((host) => {
			if (host === '') {
				return true;
			}
			return isValidIP(host) || isValidHostname(host);
		}, 'Invalid host format'),
	port: z.number().int().gte(1, 'Invalid port number').lte(65535, 'Invalid port number'),
	dbms: z.enum(['mysql', 'postgresql', 'sqlserver', 'oracle', 'mongodb', 'redis', 'mariadb']),
	username: z
		.string()
		.min(1, 'Username is required')
		.max(100, 'Username must be less than 100 characters'),
	password: z
		.string()
		.min(1, 'Password is required')
		.max(100, 'Password must be less than 100 characters')
});

export const AssetUpdateSchema = z.object({
	name: z
		.string()
		.min(2, 'Name must be at least 2 characters')
		.max(100, 'Name must be less than 100 characters'),
	description: z.string().max(255, 'Description must be less than 255 characters').nullish(),
	status: z.enum(['active', 'inactive']).default('active')
});

export const AssetCredentialsSchema = z.object({
	host: z
		.string()
		.min(1, 'Host is required')
		.max(255, 'Host must be less than 255 characters')
		.refine((host) => {
			if (host === '') {
				return true;
			}
			return isValidIP(host) || isValidHostname(host);
		}, 'Invalid host format'),
	port: z.number().int().gte(1, 'Invalid port number').lte(65535, 'Invalid port number'),
	dbms: z.enum(['mysql', 'postgresql', 'sqlserver', 'oracle', 'mongodb', 'redis', 'mariadb']),
	username: z.string().max(100, 'Username must be less than 100 characters').nullish(),
	password: z.string().max(100, 'Password must be less than 100 characters').nullish()
});

const accessRoles = ['requester', 'approver'] as const;
const accessTypes = ['user', 'user_group'] as const;

export const AssetRemoveAccessSchema = z.object({
	id: z.number().int(),
	role: z.enum(accessRoles),
	type: z.enum(accessTypes)
});

export const AssetAddAccessSchema = z.object({
	userIds: z
		.string()
		.regex(/^\d+(,\d+)*$/, 'Must be comma-separated numbers')
		.transform((val) => (val === '' ? undefined : val.split(',').map(Number)))
		.optional(),
	groupIds: z
		.string()
		.regex(/^\d+(,\d+)*$/, 'Must be comma-separated numbers')
		.transform((val) => (val === '' ? undefined : val.split(',').map(Number)))
		.optional(),
	role: z.enum(accessRoles)
});

export type Asset = z.infer<typeof AssetSchema>;
export type AssetUpdate = z.infer<typeof AssetUpdateSchema>;
export type AssetCredentials = z.infer<typeof AssetCredentialsSchema>;
export type AssetRemoveAccess = z.infer<typeof AssetRemoveAccessSchema>;
