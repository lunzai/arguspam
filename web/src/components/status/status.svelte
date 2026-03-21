<script lang="ts">
	import { Badge } from '$ui/badge';
	import { cn } from '$lib/utils';

	type Props = {
		status: string;
		label?: string | null;
		class?: string;
	};

	let {
		status,
		label = null,
		class: className,
		...restProps
	}: Props = $props();

	let color = $state('');

	$effect(() => {
		switch (status.toLowerCase()) {
			case 'active':
			case 'approved':
			case 'started':
            case 'enabled':
				color = 'bg-green-500';
				break;
			case 'inactive':
			case 'expired':
			case 'cancelled':
			case 'low':
            case 'off':
				color = 'bg-gray-500';
				break;
			case 'rejected':
			case 'critical':
				color = 'bg-red-500';
				break;
			case 'high':
			case 'terminated':
            case 'pending':
				color = 'bg-orange-500';
				break;
			case 'submitted':
			case 'medium':
			case 'ended':
				color = 'bg-blue-500';
				break;
			default:
				break;
		}
	});
</script>

<span class="inline-flex items-center gap-2" {...restProps}>
    <span class={cn('inline-block w-2 h-2 rounded-full', color)}></span>
    <span class="text-sm text-gray-700 font-medium capitalize">{label || status}</span>
</span>
