import { fail, type Actions } from '@sveltejs/kit';
import { AuthService } from '$services/auth';
import { UserService } from '$services/user';
import type { PageServerLoad } from './$types';
import { superValidate, message } from 'sveltekit-superforms';
import { loginSchema } from '$validations/auth';
import { zod } from 'sveltekit-superforms/adapters';
import { getAuthToken, setAuthToken, setCurrentOrgId } from '$utils/cookie';
import { redirect } from '@sveltejs/kit';
import { ADMIN_EMAIL, ADMIN_PASSWORD } from '$env/static/private';

export const load: PageServerLoad = async ({ cookies }) => {
	const token = getAuthToken(cookies);
	if (token) {
		return redirect(302, '/');
	}
	return {
		form: await superValidate({
			email: ADMIN_EMAIL,
			password: ADMIN_PASSWORD
		}, zod(loginSchema))
	};
};

export const actions: Actions = {
	default: async ({ request, cookies }) => {
		const form = await superValidate(request, zod(loginSchema));

		if (!form.valid) {
			return fail(422, { form });
		}

		try {
			const authService = new AuthService(getAuthToken(cookies) as string);
			const loginResponse = await authService.login(form.data.email, form.data.password);
			setAuthToken(cookies, loginResponse.data.token);
			const userService = new UserService(loginResponse.data.token);
			const orgCollection = await userService.getOrgs();
			if (orgCollection.data.length > 0) {
				setCurrentOrgId(cookies, orgCollection.data[0].attributes.id);
			}
			return message(form, 'Login successful');
		} catch (error) {
			return fail(401, { form, error: 'Invalid credentials' });
		}
	}
};
