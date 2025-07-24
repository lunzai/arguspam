import { fail, type Actions } from '@sveltejs/kit';
import { AuthService } from '$services/auth';
import { UserService } from '$services/user';
import type { PageServerLoad } from './$types';
import { superValidate, message, setError } from 'sveltekit-superforms';
import { TwoFactorVerifySchema } from '$validations/auth';
import { zod } from 'sveltekit-superforms/adapters';
import {
	getAuthToken,
	setAuthToken,
	setCurrentOrgId,
	getTempKey,
	clearTempKey
} from '$utils/cookie';
import { redirect, isRedirect } from '@sveltejs/kit';
import { ADMIN_EMAIL, ADMIN_PASSWORD } from '$env/static/private';
import { setFormErrors } from '$utils/form';

export const load: PageServerLoad = async ({ cookies }) => {
	const tempKey = getTempKey(cookies);
	if (!tempKey) {
		return redirect(302, '/');
	}
	return {
		form: await superValidate(zod(TwoFactorVerifySchema)),
		tempKey
	};
};

export const actions: Actions = {
	default: async ({ request, cookies }) => {
		const form = await superValidate(request, zod(TwoFactorVerifySchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		try {
			const authService = new AuthService(getAuthToken(cookies) as string);
			const loginResponse = await authService.verify2fa(form.data.code, form.data.temp_key);
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
			if (!token) {
				setError(form, 'code', 'Invalid OTP code');
				return fail(400, { form });
			}
			clearTempKey(cookies);
			setAuthToken(cookies, loginResponse.data.token);
			const userService = new UserService(loginResponse.data.token);
			const orgCollection = await userService.getOrgs();
			if (orgCollection.data.length > 0) {
				setCurrentOrgId(cookies, orgCollection.data[0].attributes.id);
			}
			return message(form, 'Login successful');
		} catch (error) {
			if (isRedirect(error)) {
				throw error;
			}
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(401, { form, error: 'Invalid credentials' });
		}
	}
};
