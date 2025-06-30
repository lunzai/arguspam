<script lang="ts">
	import { TableCell, TableRow } from '$ui/table';
	interface Props {
		message?: string;
	}
	let { message = 'No data available' }: Props = $props();
</script>

<TableRow>
	<TableCell colspan={100} class="h-14 text-center">
		<div class="flex items-center justify-center space-y-2">
			<span class="text-sm text-gray-500">{message}</span>
		</div>
	</TableCell>
</TableRow>
