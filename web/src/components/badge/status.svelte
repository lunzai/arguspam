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
		switch (status.toLowerCase()) {
			case 'active':
			case 'approved':
			case 'started':
            case 'low':
            case 'postgresql':
				badgeClass = 'text-green-500 bg-green-50 border-green-200';
				break;
			case 'inactive':
			case 'expired':
			case 'cancelled':
				badgeClass = 'text-gray-500 bg-gray-50 border-gray-200';
				break;
			case 'rejected':
			case 'critical':
            case 'sqlserver':
				badgeClass = 'text-red-500 bg-red-50 border-red-200';
				break;
			case 'high':
			case 'terminated':
            case 'redis':
				badgeClass = 'text-orange-500 bg-orange-50 border-orange-200';
				break;
			case 'submitted':
			case 'medium':
			case 'ended':
            case 'mysql':
				badgeClass = 'text-blue-500 bg-blue-50 border-blue-200';
				break;
            case 'oracle':
                badgeClass = 'text-purple-500 bg-purple-50 border-purple-200';
                break;
            case 'mongodb':
                badgeClass = 'text-yellow-500 bg-yellow-50 border-yellow-200';
                break;
            case 'mariadb':
                badgeClass = 'text-pink-500 bg-pink-50 border-pink-200';
                break;
			default:
                badgeClass = 'text-gray-500 bg-gray-50 border-gray-200';
				break;
		}
	});
</script>

<Badge variant="outline" class={cn('uppercase font-bold text-xs rounded', badgeClass, className)} {...restProps}>
	{label || status}
</Badge>
