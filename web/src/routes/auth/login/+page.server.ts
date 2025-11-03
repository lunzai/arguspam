import { fail, type Actions } from '@sveltejs/kit';
import { AuthService } from '$services/auth';
import { UserService } from '$services/user';
import type { PageServerLoad } from './$types';
import { superValidate, message } from 'sveltekit-superforms';
import { LoginSchema } from '$validations/auth';
import { zod4 } from 'sveltekit-superforms/adapters';
import { getAuthToken, setAuthToken, setCurrentOrgId, setTempKey } from '$utils/cookie';
import { redirect, isRedirect } from '@sveltejs/kit';
import type { User } from '$models/user';

export const load: PageServerLoad = async ({ cookies }) => {
	const token = getAuthToken(cookies);
	if (token) {
		return redirect(302, '/');
	}
	return {
		form: await superValidate(zod4(LoginSchema))
	};
};

export const actions: Actions = {
	default: async ({ request, cookies }) => {
		const form = await superValidate(request, zod4(LoginSchema));

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
				requires_2fa: boolean | null;
				token: string | null;
				temp_key: string | null;
				temp_key_expires_at: string | null;
			} = loginResponse.data;
			if (requires_2fa) {
				if (!temp_key || !temp_key_expires_at) {
					return fail(401, { form, error: 'Invalid credentials' });
				}
				setTempKey(cookies, temp_key, temp_key_expires_at);
				return redirect(302, '/auth/2fa');
			} else {
                if (!token) {
                    return fail(401, { form, error: 'Invalid credentials' });
                }
				setAuthToken(cookies, token);
				const userService = new UserService(token);
				const orgCollection = await userService.getOrgs();
				if (orgCollection.data.length > 0) {
					setCurrentOrgId(cookies, orgCollection.data[0].attributes.id);
				}
				return message(form, 'Login successful');
			}
		} catch (error) {
			if (isRedirect(error)) {
				throw error;
			}
			return fail(401, { form, error: 'Invalid credentials' });
		}
	}
};
