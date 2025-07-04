import { setError } from 'sveltekit-superforms';
import { snakeToCamel } from './string';
import type { SuperValidated } from 'sveltekit-superforms';
import type { ApiValidationErrorResponse } from '$resources/api';

export function setFormErrors(form: SuperValidated<any>, data: ApiValidationErrorResponse) {
    for (const [key, value] of Object.entries(data.errors)) {
        setError(form, snakeToCamel(key) as any, value[0]);
    }
}