import { z } from 'zod';
import { addMinutes, formatDistanceToNowStrict } from 'date-fns';
import { 
    PUBLIC_ACCESS_REQUEST_MIN_DURATION,
    PUBLIC_ACCESS_REQUEST_MAX_DURATION
} from '$env/static/public';

export const RequesterSchema = z.object({
    org_id: z.number(),
    asset_id: z.number(),
    start_datetime: z.date({
        required_error: 'Start datetime is required',
        invalid_type_error: 'Invalid date'
    }),
    end_datetime: z.date({
        required_error: 'End datetime is required',
        invalid_type_error: 'Invalid date'
    }).min(new Date(), { message: 'End datetime must be in the future' }),
    duration: z.number().nullish(),
    reason: z
        .string()
        .trim()
        .min(1, 'Reason is required'),
    intended_query: z
        .string()
        .trim()
        .min(1, 'Intended query is required'),
    scope: z.enum(['ReadOnly', 'ReadWrite', 'DDL', 'DML', 'All']),
    is_access_sensitive_data: z.boolean(),
    sensitive_data_note: z
        .string()
        .trim()
        .nullish(),
})
.refine(({ is_access_sensitive_data, sensitive_data_note }) => {
    if (!is_access_sensitive_data) {
        return true;
    }
    return sensitive_data_note && sensitive_data_note !== '';
}, {
    path: ['sensitive_data_note'],
    message: 'Sensitive data note is required',
})
.refine(({ start_datetime, end_datetime }) => start_datetime < end_datetime, {
    path: ['end_datetime'],
    message: 'End datetime must be after start datetime',
})
.refine(({ start_datetime, end_datetime }) => {
    const duration = (end_datetime.getTime() - start_datetime.getTime()) / 60000;
    return duration >= Number(PUBLIC_ACCESS_REQUEST_MIN_DURATION);
}, {
    path: ['end_datetime'],
    message: `Duration must be at least ${formatDistanceToNowStrict(
        addMinutes(Date.now(), Number(PUBLIC_ACCESS_REQUEST_MIN_DURATION))
    )}`,
})
.refine(({ start_datetime, end_datetime }) => {
    const duration = (end_datetime.getTime() - start_datetime.getTime()) / 60000;
    return duration <= Number(PUBLIC_ACCESS_REQUEST_MAX_DURATION);
}, {
    path: ['end_datetime'],
    message: `Duration must be at most ${formatDistanceToNowStrict(
        addMinutes(Date.now(), Number(PUBLIC_ACCESS_REQUEST_MAX_DURATION))
    )}`,
});

export type Requester = z.infer<typeof RequesterSchema>;

export const ApproverSchema = z.object({
    approver_note: z
        .string()
        .min(2, 'Note must be at least 2 characters')
        .nullish(),
    approver_risk_rating: z.enum(['low', 'medium', 'high', 'critical']),
});

export type Approver = z.infer<typeof ApproverSchema>;