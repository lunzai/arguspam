import type { Actions, PageServerLoad } from './$types';
import { fail } from '@sveltejs/kit';
import { superValidate } from 'sveltekit-superforms';
import { zod4 } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$utils/form';
import type { Role } from '$models/role';
import { RoleSchema } from '$validations/role';
import { Rbac } from '$lib/rbac';
import { RoleService as ModelService } from '$lib/services/role';
import { mergeParams } from '$components/datatable';
import type { ApiMeta } from '$lib/resources/api';

export const load: PageServerLoad = async ({ depends, locals, url }) => {
	depends('roles:list');
    const { authToken, currentOrgId, me } = locals;
	new Rbac(me).roleView();
	const model: Partial<Role> = {
		name: '',
		description: '',
		is_default: false
	};
	const form = await superValidate(zod4(RoleSchema));
    const modelService = new ModelService(authToken as string, currentOrgId as number);
    const response = await modelService.findAll(mergeParams({
        perPage: 20,
    }, url));
	return {
		form,
		model,
		title: 'Roles',
        list: response.data,
        meta: response.meta as ApiMeta
	};
};

export const actions = {
	save: async ({ request, locals }) => {
		const { authToken, currentOrgId, me } = locals;
		new Rbac(me).roleCreate();
		const form = await superValidate(request, zod4(RoleSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const roleService = new ModelService(authToken as string, currentOrgId as number);
			const response = await roleService.create(data);
			return {
				success: true,
				message: `Role created successfully`,
				form: form,
				model: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to create role` });
		}
	}
} satisfies Actions;
