import { superValidate } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { changePasswordSchema } from '$lib/validations/user';

export const load = async ({ parent }: any) => {
	// Get user data from parent layout
	const { user } = await parent();
	
	// Initialize form with current user data (only name, email is read-only)
	const changePasswordForm = await superValidate(zod(changePasswordSchema));

	return {
		changePasswordForm,
		user,
		title: 'Settings - Security'
	};
}; 