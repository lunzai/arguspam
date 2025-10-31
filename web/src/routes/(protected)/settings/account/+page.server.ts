import { superValidate } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { UserProfileSchema } from '$validations/user';
import { fail, type Actions } from '@sveltejs/kit';
import { UserService } from '$services/user';
import { setFormErrors } from '$utils/form';

export const load = async ({ locals }: any) => {
	const { user } = locals;
	const form = await superValidate(
		{
			name: user?.name || '',
			default_timezone: user?.default_timezone || ''
		},
		zod(UserProfileSchema)
	);
	return {
		form,
		user,
		title: 'Settings - Account'
	};
};

export const actions: Actions = {
	default: async ({ request, locals }) => {
		const { authToken } = locals;
		const form = await superValidate(request, zod(UserProfileSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		try {
			const userService = new UserService(authToken as string);
			const userResource = await userService.me();
			const userResponse = await userService.update(userResource.data.attributes.id, form.data);
			return {
				success: true,
				message: 'Profile updated successfully',
				form: form,
				user: userResponse.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: 'Failed to update profile' });
		}
	}
};
