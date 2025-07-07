<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2, Save, Loader2, LockKeyhole, LockKeyholeOpen } from '@lucide/svelte';
	import { Separator } from '$ui/separator';
	import * as DL from '$components/description-list';
	import { relativeDateTime } from '$utils/date';
	import { StatusBadge } from '$components/badge';
	import type { ResourceItem, ApiCollectionResponse } from '$resources/api';
	import type { RoleResource } from '$resources/role';
	import type { Role } from '$models/role';
	import type { Permission } from '$models/permission';
	import { Checkbox } from '$ui/checkbox';
	import { toast } from 'svelte-sonner';
	import { invalidate } from '$app/navigation';
	import FormDialog from '../form-dialog.svelte';
	import * as AlertDialog from '$ui/alert-dialog';
	import { enhance } from '$app/forms';
	import { goto } from '$app/navigation';

	let { data } = $props();
	const rolePermissionCollection = $derived(
		data.rolePermissionCollection as ApiCollectionResponse<Permission>
	);
	const permissionCollection = $derived(
		data.permissionCollection as ApiCollectionResponse<Permission>
	);
	const modelResource = $derived(data.model as RoleResource);
	const model = $derived(modelResource.data.attributes as Role);
	const hasUsers = $derived(modelResource.data.relationships?.users?.length ?? 0 > 0) as boolean;
	const modelName = 'roles';
	const modelTitle = 'Role';
	let isDefault = $derived(model.is_default ? 'Yes' : 'No');

	let permissionList = $derived(
		Object.entries(
			permissionCollection.data.reduce((groups: any, item: ResourceItem<Permission>) => {
				const key = item.attributes.name.split(':')[0];
				(groups[key] = groups[key] || []).push({
					attributes: item.attributes,
					selected: false
				});
				return groups as any;
			}, {})
		).map(([groupName, items]) => ({
			groupName: groupName as string,
			items: items as { attributes: Permission; selected: boolean }[]
		}))
	);
	let selectedPermssion = $derived(
		rolePermissionCollection.data.map((p: ResourceItem<Permission>) => p.attributes)
	);
	let savePermissionsIsLoading = $state(false);
	let savePermissionsIsLocked = $state(true);
	let editRoleDialogIsOpen = $state(false);
	let deleteRoleDialogIsOpen = $state(false);
	let deleteRoleDialogIsLoading = $state(false);

	async function handleSavePermissions() {
		try {
			savePermissionsIsLoading = true;
			const formData = new FormData();
			formData.append('permissionIds', selectedPermssion.map((p) => p.id).join(','));
			const response = await fetch('?/permissions', {
				method: 'POST',
				body: formData
			});
			const result = await response.json();
			if (result.type === 'success') {
				toast.success('Permissions saved successfully');
				invalidate('roles:data');
			} else {
				toast.error('Failed to save permissions');
			}
		} catch (error) {
			toast.error('Failed to save permissions');
		} finally {
			savePermissionsIsLoading = false;
		}
	}
</script>

<h1 class="text-2xl font-medium capitalize">{modelTitle} - #{model.id} - {model.name}</h1>
<Card.Root class="gap-3 rounded-lg py-3 shadow-none">
	<Card.Header class="flex items-center justify-between px-3">
		<Card.Title class="text-lg">{modelTitle} Details</Card.Title>
		<Card.Action>
			{#if !model.is_default}
				<Button
					variant="outline"
					size="sm"
					class="transition-all duration-200 hover:bg-blue-50 hover:text-blue-500"
					onclick={() => (editRoleDialogIsOpen = true)}
				>
					<Pencil class="h-4 w-4" />
					Edit
				</Button>
				<Button
					variant="outline"
					size="sm"
					class="text-destructive border-red-200 transition-all duration-200 hover:bg-red-50 hover:text-red-500"
					disabled={hasUsers}
					onclick={() => (deleteRoleDialogIsOpen = true)}
				>
					<Trash2 class="h-4 w-4" />
					Delete
				</Button>
			{:else}
				<Button variant="outline" size="sm" disabled>Default roles are not editable</Button>
			{/if}
		</Card.Action>
	</Card.Header>
	<Separator />
	<Card.Content class="gap-3 px-3">
		<DL.Root divider={null}>
			<DL.Row>
				<DL.Label>ID</DL.Label>
				<DL.Content>{model.id}</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Name</DL.Label>
				<DL.Content>{model.name}</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Description</DL.Label>
				<DL.Content>{model.description || '-'}</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Default Role</DL.Label>
				<DL.Content>
					<StatusBadge bind:status={isDefault} class="text-sm" />
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Created At</DL.Label>
				<DL.Content>
					{relativeDateTime(model.created_at)}
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Updated At</DL.Label>
				<DL.Content>
					{relativeDateTime(model.updated_at)}
				</DL.Content>
			</DL.Row>
		</DL.Root>
	</Card.Content>
</Card.Root>

<Card.Root class="relative gap-3 rounded-lg py-3 shadow-none">
	{#if savePermissionsIsLoading}
		<div
			class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-gray-50/50 transition-all"
		>
			<Loader2 class="h-8 w-8 animate-spin text-gray-300" />
		</div>
	{/if}
	<Card.Header class="flex items-center justify-between px-3">
		<Card.Title class="text-lg">Permissions</Card.Title>
		<Card.Action>
			<Button
				variant="outline"
				size="sm"
				class="group relative transition-all duration-200
				{savePermissionsIsLocked
					? 'border-gray-200 text-gray-500 hover:border-green-200 hover:bg-green-50 hover:text-green-500'
					: 'border-green-200 text-green-500 hover:border-gray-200 hover:bg-gray-50 hover:text-gray-500'}
				"
				onclick={() => {
					savePermissionsIsLocked = !savePermissionsIsLocked;
				}}
			>
				{#if savePermissionsIsLocked}
					<div class="relative size-4">
						<LockKeyhole
							class="absolute top-0 left-0 size-4 transition-all duration-500 group-hover:opacity-0"
						/>
						<LockKeyholeOpen
							class="absolute top-0 left-0 size-4 opacity-0 transition-all duration-500 group-hover:opacity-100"
						/>
					</div>
				{:else}
					<div class="relative size-4">
						<LockKeyholeOpen
							class="absolute top-0 left-0 size-4 transition-all duration-500 group-hover:opacity-0"
						/>
						<LockKeyhole
							class="absolute top-0 left-0 size-4 opacity-0 transition-all duration-500 group-hover:opacity-100"
						/>
					</div>
				{/if}
			</Button>
			<Button
				disabled={savePermissionsIsLocked}
				variant="outline"
				size="sm"
				class="border-blue-200 text-blue-500 
				transition-all duration-200 hover:bg-blue-50 hover:text-blue-500
				{savePermissionsIsLocked ? 'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-500' : ''}
				"
				onclick={() => {
					handleSavePermissions();
				}}
			>
				<Save class="h-4 w-4" />
				Save Permissions
			</Button>
		</Card.Action>
	</Card.Header>
	<Separator />
	<Card.Content class="gap-3 px-3 text-sm">
		<div class="flex flex-col gap-6 pt-1">
			{#each permissionList as permission (permission.groupName)}
				<div class="flex flex-col gap-2">
					<div class="font-semibold capitalize">
						{permission.items[0].attributes.description.split(':')[0].trim()}
					</div>
					<div class="grid grid-cols-4 gap-2 rounded-md border border-gray-200 p-3">
						{#each permission.items as item (item.attributes.id)}
							<div class="flex items-center gap-2">
								<Checkbox
									disabled={savePermissionsIsLocked}
									value={item.attributes.name}
									id={item.attributes.name}
									class="size-4 border-gray-300"
									checked={selectedPermssion.some((p) => p.id === item.attributes.id)}
									onCheckedChange={(checked) => {
										if (checked) {
											selectedPermssion.push(item.attributes);
										} else {
											selectedPermssion = selectedPermssion.filter(
												(p) => p.id !== item.attributes.id
											);
										}
									}}
								/>
								<label for={item.attributes.name}
									>{item.attributes.description.split(':')[1].trim()}</label
								>
							</div>
						{/each}
					</div>
				</div>
			{/each}
		</div>
	</Card.Content>
</Card.Root>

<FormDialog
	isOpen={editRoleDialogIsOpen}
	{model}
	data={data.form}
	onSuccess={async () => {
		await invalidate('roles:view');
		editRoleDialogIsOpen = false;
	}}
/>

<AlertDialog.Root bind:open={deleteRoleDialogIsOpen}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>
				{#if hasUsers}
					Unable to delete user group
				{:else}
					Are you sure?
				{/if}
			</AlertDialog.Title>
			{#if hasUsers}
				<AlertDialog.Description>
					Remove all users before deleting this user group.
				</AlertDialog.Description>
			{/if}
		</AlertDialog.Header>
		<AlertDialog.Footer>
			{#if hasUsers}
				<AlertDialog.Cancel type="reset">Ok</AlertDialog.Cancel>
			{:else}
				<form
					method="POST"
					action="?/delete"
					use:enhance={({ cancel }) => {
						if (hasUsers) {
							toast.error('Unable to delete user group');
							return cancel();
						}
						deleteRoleDialogIsLoading = true;
						return async ({ result, update }) => {
							if (result.type === 'redirect') {
								goto(result?.location || '/user-groups', { invalidateAll: true }).then(() => {
									toast.success('User group deleted successfully');
								});
							} else {
								toast.error('Failed to delete user group');
							}
							deleteRoleDialogIsLoading = false;
							deleteRoleDialogIsOpen = false;
						};
					}}
				>
					<AlertDialog.Cancel disabled={deleteRoleDialogIsLoading} type="reset"
						>Cancel</AlertDialog.Cancel
					>
					<AlertDialog.Action disabled={deleteRoleDialogIsLoading} type="submit">
						Delete
					</AlertDialog.Action>
				</form>
			{/if}
		</AlertDialog.Footer>
		{#if deleteRoleDialogIsLoading}
			<div
				class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-gray-50/50 transition-all"
			>
				<Loader2 class="h-8 w-8 animate-spin text-gray-300" />
			</div>
		{/if}
	</AlertDialog.Content>
</AlertDialog.Root>
