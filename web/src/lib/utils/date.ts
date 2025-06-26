import { format } from 'date-fns';

export function shortDate(date: string): string {
    return format(new Date(date), 'MM/dd/yyyy');
}

export function shortDateTime(date: string): string {
    return format(new Date(date), 'dd/MM/yyyy h:mm a');
}