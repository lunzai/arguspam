import { superValidate } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { ChangePasswordSchema } from '$validations/user';
import { fail, type Actions } from '@sveltejs/kit';
import { UserService } from '$services/user';
import { setFormErrors } from '$lib/utils/form';
import type { PageServerLoad } from './$types';
import { TwoFactorCodeSchema } from '$lib/validations/auth';

export const load: PageServerLoad = async ({ locals, depends }) => {
	depends('settings:security');
	let qrCode = null;
	const { authToken, currentOrgId, me } = locals;

	const userService = new UserService(authToken as string, currentOrgId as number);
	if (me.two_factor_enabled && !me.two_factor_confirmed_at) {
		qrCode = await userService.getTwoFactorQrCode(Number(me.id)).then((result) => {
			return result.data.qr_code;
		});
	}
	const changePasswordForm = await superValidate(zod(ChangePasswordSchema));
	const twoFactorVerifyForm = await superValidate(zod(TwoFactorCodeSchema));
	return {
		changePasswordForm,
		twoFactorVerifyForm,
		qrCode,
		me,
		title: 'Settings - Security'
	};
};

export const actions: Actions = {
	changePassword: async ({ request, locals }) => {
		const { authToken } = locals;
		const changePasswordForm = await superValidate(request, zod(ChangePasswordSchema));
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
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(changePasswordForm, error.response.data);
				return fail(400, { changePasswordForm });
			}
			return fail(400, { changePasswordForm, error: 'Failed to change password' });
		}
	},
	updateTwoFactor: async ({ request, locals }) => {
		const { authToken, currentOrgId, me } = locals;
		const formData = await request.formData();
		const enabled = formData.get('enabled') === '1';
		try {
			const userService = new UserService(authToken as string, currentOrgId as number);
			await userService.updateTwoFactor(Number(me.id), enabled);
			return {
				success: true
			};
		} catch (error) {
			return fail(400);
		}
	},
	removeTwoFactor: async ({ request, locals }) => {
		const { authToken, currentOrgId, me } = locals;

		try {
			const userService = new UserService(authToken as string, currentOrgId as number);
			await userService.disableTwoFactor(Number(me.id));
			return {
				success: true
			};
		} catch (error) {
			return fail(400);
		}
	},
	verifyTwoFactor: async ({ request, locals }) => {
		const { authToken, currentOrgId, me } = locals;
		const form = await superValidate(request, zod(TwoFactorCodeSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const userService = new UserService(authToken as string, currentOrgId as number);
			await userService.verifyTwoFactor(Number(me.id), data.code);
			return {
				success: true,
				message: `Two-factor authentication verified successfully`,
				form: form
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to verify two-factor authentication` });
		}
	}
};
