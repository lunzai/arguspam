import type { PageServerLoad } from './$types';
import { Rbac } from '$lib/rbac';
import { RequestService } from '$lib/services/request';
import { mergeParams } from '$components/datatable';
import type { ApiMeta } from '$lib/resources/api';

export const load: PageServerLoad = async ({ depends, locals, url }) => {
    depends('requests:list');
    const { authToken, currentOrgId, me } = locals;
    new Rbac(me).requestView();
    const modelService = new RequestService(authToken as string, currentOrgId as number);
    const response = await modelService.findAll(mergeParams({
        perPage: 20, 
        include: ['asset', 'requester', 'approver', 'rejecter'],
        sort: ['-created_at']
    }, url));
    return {
        title: 'Requests',
        list: response.data,
        meta: response.meta as ApiMeta
    };
}