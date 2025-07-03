import { fail } from '@sveltejs/kit';
import type { Actions } from './$types';
import { UserGroupService } from '$services/user-group';
import type { UserGroupResource } from '$resources/user-group';
import type { UserCollection } from '$resources/user';
import { UserService } from '$lib/services/user';

export const load = async ({ params, locals, depends }) => {
	depends('user-groups:data');
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const userGroupService = new UserGroupService(authToken as string, currentOrgId);
	const model = (await userGroupService.findById(id, {
		include: ['users']
	})) as UserGroupResource;
	const userService = new UserService(authToken as string, currentOrgId);	
	const userCollection = await userService.findAll({
	}) as UserCollection;
	return {
		model,
		title: `User Group - #${model.data.attributes.id} - ${model.data.attributes.name}`
	};
};

export const actions = {
	addUsers: async ({ request, locals, params }) => {
		try {
			const { id } = params;
			const { authToken, currentOrgId } = locals;
			const data = await request.formData();
			const userIds = data.get('userIds');
			console.log('SUBMITTED', id, userIds, data);
			const success = Math.random() > 0.7;
			if (!success) {
				return fail(400, {
					message: 'Unable to add users'
				});
			}
			return;
		}
		catch (error) {
			return fail(400, {
				message: error instanceof Error ? error.message : 'Unknown error'
			});
		}
		
		// const { userIds } = await request.json();form
		// const modelService = new UserGroupService(authToken as string, currentOrgId);
		// await modelService.addUsers(Number(id), userIds);
		// return {
		// 	success: true
		// }
	}
} satisfies Actions;
