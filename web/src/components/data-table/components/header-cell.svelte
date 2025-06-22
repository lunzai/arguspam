<script lang="ts">
	import { TableCell } from '$ui/table';

	interface Props {
		align?: 'left' | 'center' | 'right';
		width?: string;
		sortable?: boolean;
		onclick?: () => void;
		className?: string;
		children: () => any;
	}

	let { 
        align = 'left', 
        width, 
        sortable = false, 
        onclick, 
        className = '', 
        children 
    }: Props = $props();

	const alignmentClasses = $derived({
		left: 'text-left',
		center: 'text-center',
		right: 'text-right'
	});

	const cursorClass = $derived(sortable ? 'cursor-pointer hover:bg-gray-50' : '');
</script>

<TableCell
	class="px-3 py-3.5 {alignmentClasses[align]} {cursorClass} {className}"
	style={width ? `width: ${width}` : ''}
	{onclick}
>
	{@render children()}
</TableCell>
