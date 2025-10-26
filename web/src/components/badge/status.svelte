<script lang="ts">
	import { Badge } from '$ui/badge';
	import { cn } from '$lib/utils';

	type Props = {
		status: string;
		label?: string | null;
		class?: string;
	};

	let {
		status = $bindable('active'),
		label = null,
		class: className,
		...restProps
	}: Props = $props();

	let badgeClass = $state('');

	$effect(() => {
		switch (status) {
			case 'active':
			case 'approved':
			case 'started':
				badgeClass = 'text-green-500 bg-green-50 border-green-200';
				break;
			case 'inactive':
			case 'expired':
			case 'cancelled':
			case 'low':
				badgeClass = 'text-gray-500 bg-gray-50 border-gray-200';
				break;
			case 'rejected':
			case 'critical':
				badgeClass = 'text-red-500 bg-red-50 border-red-200';
				break;
			case 'high':
			case 'terminated':
				badgeClass = 'text-orange-500 bg-orange-50 border-orange-200';
				break;
			case 'submitted':
			case 'medium':
			case 'ended':
				badgeClass = 'text-blue-500 bg-blue-50 border-blue-200';
				break;
			default:
				break;
		}
	});
</script>

<Badge variant="outline" class={cn('capitalize', badgeClass, className)} {...restProps}>
	{label || status}
</Badge>
