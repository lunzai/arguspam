import type { Actions, PageServerLoad } from './$types';
import { fail } from '@sveltejs/kit';
import { superValidate } from 'sveltekit-superforms';
import { zod4 } from 'sveltekit-superforms/adapters';
import { setFormErrors } from '$utils/form';
import type { UserGroup } from '$models/user-group';
import { UserGroupSchema } from '$validations/user-group';
import { Rbac } from '$lib/rbac';
import { UserGroupService as ModelService } from '$lib/services/user-group';
import { mergeParams } from '$components/datatable';
import type { ApiMeta } from '$lib/resources/api';

export const load: PageServerLoad = async ({ locals, depends, url }) => {
	depends('user-groups:list');
    const { authToken, currentOrgId, me } = locals;
    const rbac = new Rbac(me);
	rbac.userGroupView();
	const model: Partial<UserGroup> = {
		org_id: Number(currentOrgId),
		name: '',
		description: '',
		status: 'active'
	};
	const form = await superValidate(zod4(UserGroupSchema));
    const modelService = new ModelService(authToken as string, currentOrgId as number);
    const response = await modelService.findAll(mergeParams({
        perPage: 20,
        count: ['users'],
    }, url));
	return {
		form,
		model,
		title: 'User Groups',
		canCreate: rbac.canUserGroupCreate(),
        list: response.data,
        meta: response.meta as ApiMeta,
	};
};

export const actions = {
	save: async ({ request, locals, params }) => {
		new Rbac(locals.me).userGroupCreate();
		const { authToken, currentOrgId } = locals;
		const form = await superValidate(request, zod4(UserGroupSchema));
		if (!form.valid) {
			return fail(422, { form });
		}
		const data = form.data;
		try {
			const userGroupService = new ModelService(authToken as string, currentOrgId as number);
			const response = await userGroupService.create(data);
			return {
				success: true,
				message: `User group created successfully`,
				form: form,
				model: response.data.attributes
			};
		} catch (error: any) {
			if (error.response?.status === 422) {
				setFormErrors(form, error.response.data);
				return fail(400, { form });
			}
			return fail(400, { form, error: `Failed to create user group` });
		}
	}
} satisfies Actions;
