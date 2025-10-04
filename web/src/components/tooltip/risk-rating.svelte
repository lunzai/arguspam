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
					<span class="font-bold">Low:</span> Minimal risk with limited scope, non-sensitive data, and
					short duration access.
				</li>
				<li>
					<span class="font-bold">Medium:</span> Moderate risk involving sensitive data access or write
					operations requiring additional oversight.
				</li>
				<li>
					<span class="font-bold">High:</span> Significant risk from extensive privileges, sensitive
					data modifications, or extended access duration.
				</li>
				<li>
					<span class="font-bold">Critical:</span> Severe risk requiring immediate attention due to unrestricted
					access, highly regulated data, or suspicious patterns.
				</li>
			</ul>
		</div>
	</Tooltip.Content>
</Tooltip.Root>
