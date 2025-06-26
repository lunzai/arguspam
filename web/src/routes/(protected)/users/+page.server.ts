import type { PageServerLoad } from './$types';
import { UserService } from '$services/user';

export const load: PageServerLoad = async ({ locals }) => {
    const { authToken } = locals;

    const userService = new UserService(authToken as string);
    const usersCollection = await userService.findAll();

    console.log('usersCollection', usersCollection);

    return {
        usersCollection,
        title: 'Users'
    };
};