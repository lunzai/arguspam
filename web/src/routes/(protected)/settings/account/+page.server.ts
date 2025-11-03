import { superValidate } from 'sveltekit-superforms';
import { zod4 } from 'sveltekit-superforms/adapters';
import { UserProfileSchema } from '$validations/user';
import { fail, type Actions } from '@sveltejs/kit';
import { UserService } from '$services/user';
import { setFormErrors } from '$utils/form';
import type { UserProfile } from '$lib/models/user';
import { Rbac } from '$lib/rbac';
import { USER_VIEW_ANY } from '$lib/rbac/constants';

export const load = async ({ locals }: any) => {
	const { me } = locals;
	new Rbac(me).userView();
	const form = await superValidate(
		{
			name: me?.name || '',
			default_timezone: me?.default_timezone || ''
		} as UserProfile,
		zod4(UserProfileSchema as any)
	);
	return {
		form,
		me,
		title: 'Settings - Account'
	};
};

export const actions: Actions = {
	default: async ({ request, locals }) => {
		const { authToken, me } = locals;
		new Rbac(me).userUpdate();
		const form = await superValidate(request, zod4(UserProfileSchema as any));
		if (!form.valid) {
			return fail(422, { form });
		}
		try {
			const userService = new UserService(authToken as string);
			const userResponse = await userService.update(me.id, form.data);
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
