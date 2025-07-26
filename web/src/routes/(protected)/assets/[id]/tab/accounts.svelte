<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2, Plus } from '@lucide/svelte';
	import type { AssetAccountCollection } from '$resources/asset-account';
	import type { Asset } from '$models/asset';

	interface Props {
		asset: Asset;
		list: AssetAccountCollection;
	}

	let { asset, list }: Props = $props();
</script>

<Card.Root class="w-full">
	<Card.Header>
		<Card.Title class="text-lg">Accounts</Card.Title>
		<Card.Description>Asset's accounts.</Card.Description>
		<Card.Action>
			<!-- <Button
				variant="outline"
				class="transition-all duration-200 hover:bg-blue-50 hover:text-blue-500"
			>
				<Plus class="h-4 w-4" />
				Add Account
			</Button> -->
		</Card.Action>
	</Card.Header>
	<Card.Content>
		{#each list as { attributes: row }, index (row.id)}
			<div class="flex items-center justify-between">
				<div class="flex items-center gap-2">
					<div class="flex flex-col">
						<div class="text-sm font-medium">{row.username}</div>
						<div class="text-xs text-gray-500">{row.type}</div>
						<div class="text-xs text-gray-500">{row.expires_at}</div>
						<div class="text-xs text-gray-500">{row.is_active}</div>
					</div>
				</div>
			</div>
		{/each}
	</Card.Content>
</Card.Root>
