import { superValidate } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { changePasswordSchema } from '$validations/user';
import { getAuthToken } from '$utils/cookie';
import { redirect } from '@sveltejs/kit';
import { fail, type Actions } from '@sveltejs/kit';
import { UserService } from '$services/user';

export const load = async ({ parent, cookies }: any) => {
    if (!getAuthToken(cookies)) {
		return redirect(302, '/');
	}
	// const { user } = await parent();
	const form = await superValidate(zod(changePasswordSchema));

	return {
		form,
		// user,
		title: 'Settings - Forget Password'
	};
}; 

export const actions: Actions = {
	default: async ({ request, cookies }) => {
        const form = await superValidate(request, zod(changePasswordSchema));
        if (!form.valid) {
			return fail(422, { form });
		}
        try {
            const userService = new UserService(getAuthToken(cookies) as string);
            await userService.changePassword(
                form.data.currentPassword, 
                form.data.newPassword, 
                form.data.confirmNewPassword
            );
            return {
                success: true,
                message: 'Password updated successfully',
                form: form,
                // user: userResponse.data.attributes
            }
        } catch (error) {
            return fail(400, { form, error: 'Failed to change password' });
        }
	}
};