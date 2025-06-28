import { redirect } from '@sveltejs/kit';
import { UserService } from '$services/user';
import type { UserResource } from '$lib/resources/user';

export const load = async ({ params, locals }) => {
    const { id } = params;
    const { authToken, currentOrgId } = locals;
    const userService = new UserService(authToken as string, currentOrgId);
    const user = await userService.findById(id, { include: ['orgs', 'roles', 'userGroups'] }) as UserResource;
    return { 
        user,
        title: `User - #${user.data.attributes.id} - ${user.data.attributes.name}`
    };
};