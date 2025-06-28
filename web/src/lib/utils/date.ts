import { format, formatDistanceToNow } from 'date-fns';

export function shortDate(date: string): string {
    return format(new Date(date), 'MM/dd/yyyy');
}

export function shortDateTime(date: string): string {
    return format(new Date(date), 'dd/MM/yyyy h:mm a');
}

export function relativeDateTime(date: string | null | undefined, showDatetime = true, emptyValue: string = '-'): string {
    if (!date) {
        return emptyValue;
    }
    return `${formatDistanceToNow(new Date(date))} ago ${showDatetime ? `(${shortDateTime(date)})` : ''}`;
}