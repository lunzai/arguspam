import { superValidate, setError } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { changePasswordSchema } from '$validations/user';
import { fail, type Actions } from '@sveltejs/kit';
import { UserService } from '$services/user';
import type { ApiValidationErrorResponse } from '$resources/api';
import { snakeToCamel } from '$utils/string';

export const load = async () => {
	const changePasswordForm = await superValidate(zod(changePasswordSchema));

	return {
		changePasswordForm,
		title: 'Settings - Security'
	};
};

export const actions: Actions = {
	changePassword: async ({ request, locals }) => {
		const { authToken } = locals;
		const changePasswordForm = await superValidate(request, zod(changePasswordSchema));
		if (!changePasswordForm.valid) {
			return fail(422, { changePasswordForm });
		}
		try {
			const userService = new UserService(authToken as string);
			await userService.changePassword(
				changePasswordForm.data.currentPassword,
				changePasswordForm.data.newPassword,
				changePasswordForm.data.confirmNewPassword
			);
			return {
				success: true,
				message: 'Password updated successfully',
				changePasswordForm: changePasswordForm
				// user: userResponse.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				const data: ApiValidationErrorResponse = error.response.data;
				for (const [key, value] of Object.entries(data.errors)) {
					setError(changePasswordForm, snakeToCamel(key) as any, value[0]);
				}
				return fail(400, { changePasswordForm });
			}
			return fail(400, { changePasswordForm, error: 'Failed to change password' });
		}
	}
};
