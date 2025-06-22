<script lang="ts">
	import { TableCell, TableRow } from '$ui/table';
	interface Props {
		message?: string;
	}
	let { message = 'No data available' }: Props = $props();
</script>

<TableRow>
	<TableCell colspan={100} class="h-24 text-center">
		<div class="flex flex-col items-center justify-center space-y-2">
			<div class="text-gray-400">
				<svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path
						stroke-linecap="round"
						stroke-linejoin="round"
						stroke-width="2"
						d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
					/>
				</svg>
			</div>
			<p class="text-sm text-gray-500">{message}</p>
		</div>
	</TableCell>
</TableRow>
