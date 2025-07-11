import { UserService } from '$services/user';
import type { UserResource } from '$lib/resources/user';
import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ params, locals }) => {
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	// const userService = new UserService(authToken as string, currentOrgId);
	// const user = (await userService.findById(id, {
	// 	include: ['orgs', 'roles', 'userGroups']
	// })) as UserResource;
	// console.log('server:user',user);
	// return {
	// 	model: user,
	// 	title: `User - #${user.data.attributes.id} - ${user.data.attributes.name}`
	// };
};
