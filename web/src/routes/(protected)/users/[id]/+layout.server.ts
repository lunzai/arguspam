import { UserService } from '$services/user';
import type { ApiUserResource } from '$resources/user';
import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ params, locals }) => {
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const userService = new UserService(authToken as string, currentOrgId);
	const user = (await userService.findById(id, {
		include: ['orgs', 'roles', 'userGroups']
	})) as ApiUserResource;
	return {
		model: user,
		title: `User - #${user.data.attributes.id} - ${user.data.attributes.name}`
	};
};
