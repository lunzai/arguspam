import { UserService } from '$services/user';
import type { ApiUserResource } from '$resources/user';
import type { LayoutServerLoad } from './$types';
import { Rbac } from '$lib/rbac';

export const load: LayoutServerLoad = async ({ params, locals }) => {
	const { id } = params;
	const { authToken, currentOrgId, me } = locals;
	const rbac = new Rbac(me);
	rbac.userViewAny();
	const userService = new UserService(authToken as string, currentOrgId as number);
	const user = (await userService.findById(id, {
		include: ['orgs', 'roles', 'userGroups']
	})) as ApiUserResource;
	return {
		model: user,
		canUserResetPasswordAny: rbac.canUserResetPasswordAny(),
		canUserEnrollTwoFactorAuthenticationAny: rbac.canUserEnrollTwoFactorAuthenticationAny(),
		canUserUpdateAny: rbac.canUserUpdateAny(),
		canUserViewAny: rbac.canUserViewAny(),
		canUserDeleteAny: rbac.canUserDeleteAny(),
		title: `User - #${user.data.attributes.id} - ${user.data.attributes.name}`
	};
};
