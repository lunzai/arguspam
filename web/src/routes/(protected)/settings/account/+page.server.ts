import { superValidate, setError } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { userProfileSchema } from '$validations/user';
import { fail, type Actions } from '@sveltejs/kit';
import { UserService } from '$services/user';
import { authStore } from '$stores/auth';
import type { User } from '$models/user';
import type { ApiValidationErrorResponse } from '$resources/api';
import { setFormErrors } from '$lib/utils/form';

export const load = async ({ locals }: any) => {
	const { user } = locals;
	const form = await superValidate(
		{
			name: user?.name || ''
		},
		zod(userProfileSchema)
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
		const form = await superValidate(request, zod(userProfileSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		try {
			const userService = new UserService(authToken as string);
			const userResource = await userService.me();
			const userResponse = await userService.update(userResource.data.attributes.id, form.data);
			authStore.setUser(userResponse.data.attributes as User);
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
