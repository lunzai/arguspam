import type { PageServerLoad } from './$types';
import { Rbac } from '$lib/rbac';
import { PermissionService } from '$lib/services/permission';
import { mergeParams } from '$components/datatable';
import type { ApiMeta } from '$lib/resources/api';

export const load: PageServerLoad = async ({ depends, locals, url }) => {
    depends('permissions:list');
    const { authToken, currentOrgId, me } = locals;
    new Rbac(me).permissionView();
    const modelService = new PermissionService(authToken as string, currentOrgId as number);
    const response = await modelService.findAll(mergeParams({
        perPage: 20
    }, url));
    return {
        title: 'Permissions',
        list: response.data,
        meta: response.meta as ApiMeta
    };
}
