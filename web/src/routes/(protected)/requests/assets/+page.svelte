<script lang="ts">
	import type { ApiAssetCollection } from '$lib/resources/asset.js';
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Separator } from '$ui/separator';
	import FormDialog from './form-dialog.svelte';
	import type { Asset } from '$models/asset';
	import type { Request } from '$models/request';
	import { goto } from '$app/navigation';
	import { SquareArrowOutUpRight, Database, Network } from '@lucide/svelte';

	let { data }: { data: any } = $props();
	let assetCollection = $derived(data.assetCollection as ApiAssetCollection);
	let addRequestDialogIsOpen = $state(false);
	let selectedAsset = $state<Asset | null>(null);

	function handleCreateRequest(asset: Asset) {
		selectedAsset = asset;
		addRequestDialogIsOpen = true;
	}
</script>

<h1 class="text-2xl font-medium capitalize">Create Request</h1>

<FormDialog
	bind:isOpen={addRequestDialogIsOpen}
	asset={selectedAsset as Asset}
	data={data.form}
	onSuccess={async (data: Request) => {
		await goto(`/requests/${data.id}`);
		addRequestDialogIsOpen = false;
		selectedAsset = null;
	}}
/>

<Card.Root class="w-full">
	<Card.Header>
		<Card.Title class="text-lg">Your Assets</Card.Title>
		<Card.Description>Assets assigned to you.</Card.Description>
		<Card.Action></Card.Action>
	</Card.Header>
	{#if assetCollection.data.length > 0}
		<div class="">
			<ul role="list" class="divide-y divide-gray-200">
				{#each assetCollection.data as item}
					<li class="flex items-center justify-between gap-x-3 px-6 py-4 hover:bg-gray-50">
						<div class="flex min-w-0 flex-col gap-1.5">
							<span class="truncate font-semibold">{item.attributes.name}</span>
							{#if item.attributes.description}
								<span class="text-muted-foreground truncate">
									{item.attributes.description}
								</span>
							{/if}
							<div class="flex min-h-6 items-center gap-1.5 truncate text-sm/5 text-gray-500">
								<span class="flex items-center gap-2">
									<Database class="h-3 w-3" />
									{item.attributes.dbms}
								</span>
								<Separator class="border-gray-200" orientation="vertical" />
								<span class="flex items-center gap-2">
									<Network class="h-3 w-3" />
									{item.attributes.host}:{item.attributes.port}
								</span>
							</div>
						</div>
						<div class="flex shrink-0 flex-col items-end gap-2">
							<Button
								variant="outline"
								class="transition-all duration-200"
								target="_blank"
								onclick={() => handleCreateRequest(item.attributes)}
							>
								<SquareArrowOutUpRight class="h-4 w-4" /> Create Request
							</Button>
						</div>
					</li>
				{/each}
			</ul>
		</div>
	{:else}
		<Card.Content>
			<div>
				<p class="text-gray-500">No asset assigned to you.</p>
			</div>
		</Card.Content>
	{/if}
</Card.Root>
