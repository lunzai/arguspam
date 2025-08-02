<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2, RotateCcwKey } from '@lucide/svelte';
	import * as DL from '$components/description-list';
	import { relativeDateTime } from '$utils/date';
	import { StatusBadge, BlueBadge } from '$components/badge';
	import { Separator } from '$ui/separator';
	import { Badge } from '$ui/badge';
	import * as AlertDialog from '$ui/alert-dialog';
	import { enhance } from '$app/forms';
	import { invalidate } from '$app/navigation';
	import { toast } from 'svelte-sonner';
	import Loader from '$components/loader.svelte';
	import type { Asset } from '$models/asset';
	import type { AssetAccountResource } from '$lib/resources/asset-account';
	import { goto } from '$app/navigation';
	import EditFormDialog from './edit-form-dialog.svelte';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import { AssetSchema, AssetCredentialsSchema } from '$validations/asset';
	import CredentialFormDialog from './credential-form-dialog.svelte';

	let { model, accounts, editForm, credentialsForm, modelResource } = $props();

	let deleteAssetDialogIsOpen = $state(false);
	let deleteAssetDialogIsLoading = $state(false);
	let editAssetDialogIsOpen = $state(false);
	let editAssetDialogIsLoading = $state(false);
	let editCredentialsDialogIsOpen = $state(false);
	let editCredentialsDialogIsLoading = $state(false);
	const assetHasJitAccounts = $derived(
		accounts.some((row: AssetAccountResource) => row.attributes.type === 'jit')
	);
</script>

<Card.Root class="w-full">
	<Card.Header>
		<Card.Title class="text-lg">#{model.id} - {model.name}</Card.Title>
	</Card.Header>
	<Card.Content class="space-y-4">
		<div class="flex gap-1.5">
			<BlueBadge class="text-sm">
				{model.dbms}
			</BlueBadge>
			<StatusBadge bind:status={model.status} class="text-sm" />
		</div>
		<DL.Root divider={null} dlClass="space-y-4">
			<DL.Row orientation="vertical">
				<DL.Label>Host</DL.Label>
				<DL.Content>{model.host}</DL.Content>
			</DL.Row>
			<DL.Row orientation="vertical">
				<DL.Label>Port</DL.Label>
				<DL.Content>{model.port}</DL.Content>
			</DL.Row>
			<DL.Row orientation="vertical">
				<DL.Label>Description</DL.Label>
				<DL.Content>{model.description || '-'}</DL.Content>
			</DL.Row>
			<DL.Row orientation="vertical">
				<DL.Label>Created At</DL.Label>
				<DL.Content>{relativeDateTime(model.created_at, false)}</DL.Content>
			</DL.Row>
			<DL.Row orientation="vertical">
				<DL.Label>Updated At</DL.Label>
				<DL.Content>{relativeDateTime(model.updated_at, false)}</DL.Content>
			</DL.Row>
		</DL.Root>
	</Card.Content>
	<Separator />
	<Card.Footer class="flex-col gap-2">
		<Button
			variant="outline"
			class="w-full transition-all duration-200 hover:bg-blue-50 hover:text-blue-500"
			onclick={() => (editAssetDialogIsOpen = true)}
		>
			<Pencil class="h-4 w-4" />
			Edit Information
		</Button>
		<Button
			variant="outline"
			class="w-full transition-all duration-200 hover:bg-blue-50 hover:text-blue-500"
			onclick={() => (editCredentialsDialogIsOpen = true)}
		>
			<RotateCcwKey class="h-4 w-4" />
			Edit Credentials
		</Button>
		<Button
			variant="outline"
			class="text-destructive w-full border-red-200 transition-all duration-200 hover:bg-red-50 hover:text-red-500"
			onclick={() => (deleteAssetDialogIsOpen = true)}
		>
			<Trash2 class="h-4 w-4" />
			Delete
		</Button>
	</Card.Footer>
</Card.Root>

<EditFormDialog
	bind:isOpen={editAssetDialogIsOpen}
	{model}
	bind:data={editForm}
	onSuccess={async (data: Asset) => {
		editAssetDialogIsOpen = false;
	}}
/>

<CredentialFormDialog
	bind:isOpen={editCredentialsDialogIsOpen}
	{model}
	bind:data={credentialsForm}
	onSuccess={async (data: Asset) => {
		editCredentialsDialogIsOpen = false;
	}}
/>



<AlertDialog.Root bind:open={deleteAssetDialogIsOpen}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>
				{#if assetHasJitAccounts}
					Unable to delete asset
				{:else}
					Are you sure?
				{/if}
			</AlertDialog.Title>
			{#if assetHasJitAccounts}
				<AlertDialog.Description>
					Stop / terminate all JIT accounts before deleting this asset.
				</AlertDialog.Description>
			{/if}
		</AlertDialog.Header>
		<AlertDialog.Footer>
			{#if assetHasJitAccounts}
				<AlertDialog.Cancel type="reset">Ok</AlertDialog.Cancel>
			{:else}
				<form
					method="POST"
					action="?/delete"
					use:enhance={({ cancel }) => {
						if (assetHasJitAccounts) {
							toast.error('Unable to delete asset');
							return cancel();
						}
						deleteAssetDialogIsLoading = true;
						return async ({ result, update }) => {
							if (result.type === 'redirect') {
								goto(result?.location || '/assets', {
									invalidateAll: true
								}).then(() => {
									toast.success('Asset deleted successfully');
								});
								deleteAssetDialogIsLoading = false;
								deleteAssetDialogIsOpen = false;
							} else {
								toast.error('Failed to delete asset');
							}
							deleteAssetDialogIsLoading = false;
							deleteAssetDialogIsOpen = false;
						};
					}}
				>
					<AlertDialog.Action type="submit">Delete</AlertDialog.Action>
				</form>
			{/if}
		</AlertDialog.Footer>
		{#if deleteAssetDialogIsLoading}
			<Loader show={deleteAssetDialogIsLoading} />
		{/if}
	</AlertDialog.Content>
</AlertDialog.Root>
