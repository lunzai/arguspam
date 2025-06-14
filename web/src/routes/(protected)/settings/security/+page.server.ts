import { fail } from '@sveltejs/kit';
import { superValidate } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { changePasswordSchema } from '$lib/validations/user';
import { ServerApi } from '$lib/api/server';
import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent }) => {
	const { user } = await parent();
	const changePasswordForm = await superValidate(zod(changePasswordSchema));

	return {
		changePasswordForm,
		user,
		title: 'Settings - Security'
	};
};

export const actions: Actions = {
	changePassword: async ({ request, cookies }) => {
		const form = await superValidate(request, zod(changePasswordSchema));

		if (!form.valid) {
			return fail(400, { form });
		}

		try {
			const token = cookies.get('auth_token'); // or however you store auth
			// await serverApi.request('/auth/change-password', {
			// 	method: 'POST',
			// 	body: form.data,
			// 	token
			// });

			return { form, success: true };
		} catch (error) {
			return fail(500, { 
				form, 
				error: 'Failed to change password' 
			});
		}
	}
}; 