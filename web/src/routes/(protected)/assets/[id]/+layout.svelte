<script lang="ts">
	import { Separator } from '$ui/separator';
	import Sidebar from './sidebar.svelte';
	import type { AssetAccountCollection } from '$resources/asset-account';

	let { data, children } = $props();

	const model = $derived(data.asset);
	const accounts = $derived(data.model.data.relationships?.accounts as AssetAccountCollection);
	const editForm = $derived(data.editForm);
	const credentialsForm = $derived(data.credentialsForm);
	const canUpdate = $derived(data.canUpdate);
	const canUpdateAdminAccount = $derived(data.canUpdateAdminAccount);
	const canDelete = $derived(data.canDelete);
	const canAddAccessGrant = $derived(data.canAddAccessGrant);
	const canRemoveAccessGrant = $derived(data.canRemoveAccessGrant);
	const canTestConnection = $derived(data.canTestConnection);
</script>

<div class="flex flex-col space-y-4">
	<h1 class="text-xl font-medium capitalize">Asset #{model.id} - {model.name}</h1>
	<Separator />
	<div class="mt-2 flex flex-col gap-6 lg:flex-row">
		<aside class="w-full lg:w-64">
			<Sidebar
				{model}
				{accounts}
				{editForm}
				{credentialsForm}
				{canUpdate}
				{canUpdateAdminAccount}
				{canDelete}
			/>
		</aside>
		<Separator orientation="vertical" class="hidden lg:block" />
		<div class="min-w-0 flex-1">
			{@render children()}
		</div>
	</div>
</div>
