import type { LayoutServerLoad } from './$types';
import { Rbac } from '$lib/rbac';

export const load: LayoutServerLoad = async ({ locals }) => {
	const { me } = locals;
	const rbac = new Rbac(me);
	return {
		canChangePassword: rbac.canUserChangePassword(),
		canEnrollTwoFactor: rbac.canUserEnrollTwoFactorAuthentication(),
		canUpdateProfile: rbac.canUserUpdate()
	};
};
