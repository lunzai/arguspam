import { fail, type Actions } from '@sveltejs/kit';
import { AuthService } from '$services/auth';
import { UserService } from '$services/user';
import type { PageServerLoad } from './$types';
import { superValidate, message, setError } from 'sveltekit-superforms';
import { TwoFactorVerifySchema } from '$validations/auth';
import { zod4 } from 'sveltekit-superforms/adapters';
import {
	getAuthToken,
	setAuthToken,
	setCurrentOrgId,
	getTempKey,
	clearTempKey
} from '$utils/cookie';
import { redirect, isRedirect } from '@sveltejs/kit';
import { setFormErrors } from '$utils/form';
import type { User } from '$models/user';

export const load: PageServerLoad = async ({ cookies }) => {
	const tempKey = getTempKey(cookies);
	if (!tempKey) {
		return redirect(302, '/');
	}
	return {
		form: await superValidate(zod4(TwoFactorVerifySchema)),
		tempKey
	};
};

export const actions: Actions = {
	default: async ({ request, cookies }) => {
		const form = await superValidate(request, zod4(TwoFactorVerifySchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		try {
			const authService = new AuthService(getAuthToken(cookies) as string);
			const loginResponse = await authService.verify2fa(form.data.code, form.data.temp_key);
			const {
				user,
				token
			}: {
				user: User;
				token: string | null;
			} = loginResponse.data;
			if (!token) {
				setError(form, 'code', 'Invalid OTP code');
				return fail(400, { form });
			}
			clearTempKey(cookies);
			setAuthToken(cookies, token);
			const userService = new UserService(token);
			const orgCollection = await userService.getOrgs();
			if (orgCollection.data.length > 0) {
				setCurrentOrgId(cookies, orgCollection.data[0].attributes.id);
			}
			return message(form, 'Login successful');
		} catch (err: any) {
			if (isRedirect(err)) {
				throw err;
			}
			if (err.response?.status === 422) {
				setFormErrors(form, err.response.data);
				return fail(400, { form });
			}
			return fail(401, { form, error: 'Invalid credentials' });
		}
	}
};
