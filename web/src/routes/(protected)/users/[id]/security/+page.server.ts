import type { ApiUserResource } from '$resources/user';
import { UserService } from '$services/user';
import { superValidate } from 'sveltekit-superforms';
import type { Actions } from '@sveltejs/kit';
import { zod4 } from 'sveltekit-superforms/adapters';
import { fail } from '@sveltejs/kit';
import type { User } from '$models/user';
import type { PageServerLoad } from './$types';
import { TwoFactorCodeSchema } from '$validations/auth';
import { setFormErrors } from '$utils/form';
import { ResetPasswordSchema } from '$validations/user';

export const load: PageServerLoad = async ({ params, locals, depends, parent }) => {
	depends('user:view:security');
	const { id } = params;
	const { authToken, currentOrgId } = locals;
	const data = await parent();
	const model = data.model as ApiUserResource;
	const user = model.data.attributes as User;
	const userService = new UserService(authToken as string, currentOrgId);
	let qrCode = null;
	if (user.two_factor_enabled && !user.two_factor_confirmed_at) {
		qrCode = await userService.getTwoFactorQrCode(Number(id)).then((result) => {
			return result.data.qr_code;
		});
	}
	const twoFactorVerifyForm = await superValidate(zod4(TwoFactorCodeSchema));
	const resetPasswordForm = await superValidate(zod4(ResetPasswordSchema));
	return {
		twoFactorVerifyForm,
		resetPasswordForm,
		authUser: data.user,
		model,
		qrCode,
		title: `User - #${model.data.attributes.id} - ${model.data.attributes.name}`
	};
};

export const actions = {
	resetPassword: async ({ request, locals, params }) => {
		const { authToken, currentOrgId } = locals;
		const { id } = params;
		const form = await superValidate(request, zod4(ResetPasswordSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const userService = new UserService(authToken as string, currentOrgId);
			await userService.resetPassword(Number(id), data.newPassword, data.confirmNewPassword);
			return {
				success: true,
				message: `Password reset successfully`,
				form: form
			};
		} catch (error) {
			return fail(400, { form, error: `Failed to reset password` });
		}
	},
	updateTwoFactor: async ({ request, locals, params }) => {
		const { authToken, currentOrgId } = locals;
		const { id } = params;
		const formData = await request.formData();
		const enabled = formData.get('enabled') === '1';
		try {
			const userService = new UserService(authToken as string, currentOrgId);
			await userService.updateTwoFactor(Number(id), enabled);
			return {
				success: true
			};
		} catch (error) {
			return fail(400);
		}
	},
	removeTwoFactor: async ({ request, locals, params }) => {
		const { authToken, currentOrgId } = locals;
		const { id } = params;
		try {
			const userService = new UserService(authToken as string, currentOrgId);
			await userService.disableTwoFactor(Number(id));
			return {
				success: true
			};
		} catch (error) {
			return fail(400);
		}
	},
	verifyTwoFactor: async ({ request, locals, params }) => {
		const { authToken, currentOrgId } = locals;
		const { id } = params;
		const form = await superValidate(request, zod4(TwoFactorCodeSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const userService = new UserService(authToken as string, currentOrgId);
			await userService.verifyTwoFactor(Number(id), data.code);
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
} satisfies Actions;
