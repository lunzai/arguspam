<script lang="ts">
	import * as Tooltip from '$ui/tooltip';
	import type { Asset } from '$models/asset';

	let { children = null, asset }: { children?: any; asset: Asset } = $props();
</script>

<Tooltip.Root>
	<Tooltip.Root>
		<Tooltip.Trigger class="text-left">
			{#if children}
				{@render children()}
			{:else}
				<span class="underline decoration-dashed">{asset.name} ({asset.dbms})</span>
			{/if}
		</Tooltip.Trigger>
		<Tooltip.Content>
			<div class="">
				<ul>
					<li>
						<span class="font-bold">Host:</span>
						{asset.host}
					</li>
					<li>
						<span class="font-bold">Port:</span>
						{asset.port}
					</li>
				</ul>
			</div>
		</Tooltip.Content>
	</Tooltip.Root>
</Tooltip.Root>
