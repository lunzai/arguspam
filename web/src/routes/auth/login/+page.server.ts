import { fail, type Actions } from '@sveltejs/kit';
import { AuthService } from '$services/auth';
import { UserService } from '$services/user';
import type { PageServerLoad } from './$types';
import { superValidate, message } from 'sveltekit-superforms';
import { LoginSchema } from '$validations/auth';
import { zod } from 'sveltekit-superforms/adapters';
import { getAuthToken, setAuthToken, setCurrentOrgId, setTempKey } from '$utils/cookie';
import { redirect, isRedirect } from '@sveltejs/kit';
import { ADMIN_EMAIL, ADMIN_PASSWORD } from '$env/static/private';

export const load: PageServerLoad = async ({ cookies }) => {
	const token = getAuthToken(cookies);
	if (token) {
		return redirect(302, '/');
	}
	return {
		form: await superValidate(
			{
				email: ADMIN_EMAIL,
				password: ADMIN_PASSWORD
			},
			zod(LoginSchema)
		)
	};
};

export const actions: Actions = {
	default: async ({ request, cookies }) => {
		const form = await superValidate(request, zod(LoginSchema));

		if (!form.valid) {
			return fail(422, { form });
		}

		try {
			const authService = new AuthService(getAuthToken(cookies) as string);
			const loginResponse = await authService.login(form.data.email, form.data.password);
			const {
				user,
				requires_2fa,
				token,
				temp_key,
				temp_key_expires_at
			}: {
				user: User;
				requires_2fa: boolean;
				token: string | null;
				temp_key: string | null;
				temp_key_expires_at: Date | null;
			} = loginResponse.data;
			if (requires_2fa) {
				if (!temp_key || !temp_key_expires_at) {
					return fail(401, { form, error: 'Invalid credentials' });
				}
				setTempKey(cookies, temp_key, temp_key_expires_at);
				return redirect(302, '/auth/2fa');
			} else {
				setAuthToken(cookies, loginResponse.data.token);
				const userService = new UserService(loginResponse.data.token);
				const orgCollection = await userService.getOrgs();
				if (orgCollection.data.length > 0) {
					setCurrentOrgId(cookies, orgCollection.data[0].attributes.id);
				}
				return message(form, 'Login successful');
			}
			return fail(401, { form, error: 'Debugging' });
		} catch (error) {
			if (isRedirect(error)) {
				throw error;
			}
			return fail(401, { form, error: 'Invalid credentials' });
		}
	}
};
