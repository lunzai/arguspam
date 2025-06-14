import { superValidate, message } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { userProfileSchema } from '$validations/user';
import { getAuthToken } from '$utils/cookie';
import { redirect } from '@sveltejs/kit';
import { fail, type Actions } from '@sveltejs/kit';
import { UserService } from '$services/user';
import { authStore } from '$stores/auth';
import type { User } from '$models/user';

export const load = async ({ parent, cookies }: any) => {
    if (!getAuthToken(cookies)) {
		return redirect(302, '/');
	}
	const { user } = await parent();
	const form = await superValidate({
		name: user?.name || ''
	}, zod(userProfileSchema));

	return {
		form,
		user,
		title: 'Settings - Account'
	};
}; 

export const actions: Actions = {
	default: async ({ request, cookies }) => {
        const form = await superValidate(request, zod(userProfileSchema));
        if (!form.valid) {
			return fail(422, { form });
		}
        try {
            const userService = new UserService(getAuthToken(cookies) as string);
            const userResource = await userService.me();
            const userResponse = await userService.update(userResource.data.attributes.id, form.data);
            authStore.setUser(userResponse.data.attributes as User);
            return {
                success: true,
                message: 'Profile updated successfully',
                form: form,
                user: userResponse.data.attributes
            }
        } catch (error) {
            return fail(400, { form, error: 'Failed to update profile' });
        }
	}
};