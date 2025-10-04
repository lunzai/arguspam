import { format, formatDistanceToNow } from 'date-fns';

export function shortDate(date: string | Date): string {
	return format(new Date(date), 'MM/dd/yyyy');
}

export function shortDateTime(date: string | Date): string {
	return format(new Date(date), 'dd/MM/yyyy h:mm a');
}

export function shortDateTimeRange(start: string | Date, end: string | Date): string {
	start = new Date(start);
	end = new Date(end);
	const sameDate =
		start.getFullYear() === end.getFullYear() &&
		start.getMonth() === end.getMonth() &&
		start.getDate() === end.getDate();
	return sameDate
		? `${shortDateTime(start)} - ${format(end, 'h:mm a')}`
		: `${shortDateTime(start)} - ${shortDateTime(end)}`;
}

export function relativeDateTime(
	date: string | null | undefined | Date,
	showDatetime = true,
	emptyValue: string = '-'
): string {
	if (!date) {
		return emptyValue;
	}
	const isFuture = new Date(date) > new Date();
	return `${formatDistanceToNow(new Date(date))} ${isFuture ? '' : 'ago'} ${showDatetime ? `(${shortDateTime(date)})` : ''}`;
}
