import type { Role } from '$models/role';
import type { Permission } from '$models/permission';

export interface RbacUser {
	roles: Role[];
	permissions: Permission[];
}
