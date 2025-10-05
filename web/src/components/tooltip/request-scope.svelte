<script lang="ts">
	import * as Tooltip from '$ui/tooltip';
	import { CircleHelp } from '@lucide/svelte';

	let { children = null } = $props();
</script>

<Tooltip.Root>
	<Tooltip.Trigger>
		{#if children}
			{@render children()}
		{:else}
			<CircleHelp class="size-3" />
		{/if}
	</Tooltip.Trigger>
	<Tooltip.Content>
		<div class="">
			<ul>
				<li>
					<span class="font-bold">ReadOnly:</span> View data only (SELECT queries)
				</li>
				<li>
					<span class="font-bold">ReadWrite:</span> View and modify data (SELECT, INSERT, UPDATE, DELETE)
				</li>
				<li>
					<span class="font-bold">DDL:</span> Manage database structure (CREATE, ALTER, DROP tables/indexes)
				</li>
				<li>
					<span class="font-bold">DML:</span> Manipulate data operations (INSERT, UPDATE, DELETE, SELECT)
				</li>
				<li>
					<span class="font-bold">All:</span> Full database access (all operations and administrative
					functions)
				</li>
			</ul>
		</div>
	</Tooltip.Content>
</Tooltip.Root>
