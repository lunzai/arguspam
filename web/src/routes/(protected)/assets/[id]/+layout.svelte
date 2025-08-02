<script lang="ts">
	import type { ApiAssetResource } from '$resources/asset';
	import type { Asset } from '$models/asset';
	import { Separator } from '$ui/separator';
	import Sidebar from './sidebar.svelte';
	import type { AssetAccountCollection } from '$resources/asset-account';

	let { data, children } = $props();

	const modelResource = $derived(data.model as ApiAssetResource);
	const model = $derived(data.asset);
	const accounts = $derived(data.model.data.relationships?.accounts as AssetAccountCollection);
	const editForm = $derived(data.editForm);
	const credentialsForm = $derived(data.credentialsForm);
</script>

<div class="flex flex-col space-y-4">
	<h1 class="text-xl font-medium capitalize">Asset #{model.id} - {model.name}</h1>
	<Separator />
	<div class="mt-2 flex flex-col gap-6 lg:flex-row">
		<aside class="w-full lg:w-64">
			<Sidebar {model} {accounts} {modelResource} {editForm} {credentialsForm} />
		</aside>
		<Separator orientation="vertical" />
		<div class="flex-1">
			{@render children()}
		</div>
	</div>
</div>
