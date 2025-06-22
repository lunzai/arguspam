<script lang="ts">
	import { TableCell } from '$ui/table';

	interface Props {
		align?: 'left' | 'center' | 'right';
		className?: string;
		children: () => any;
	}

	let { align = 'left', className = '', children }: Props = $props();

	const alignmentClasses = $derived({
		left: 'text-left',
		center: 'text-center',
		right: 'text-right'
	});
</script>

<TableCell class="px-3 py-4 {alignmentClasses[align]} {className}">
	{@render children()}
</TableCell>
