<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2, UserPlus } from '@lucide/svelte';
	import * as DL from '$components/description-list';
	import { relativeDateTime } from '$utils/date';
	import { StatusBadge } from '$components/badge';
	import { toast } from 'svelte-sonner';
	import type { ApiOrgResource } from '$resources/org';
	import type { Org } from '$models/org';
	import type { User } from '$models/user';
	import { invalidate, goto } from '$app/navigation';
	import type { ColumnDef } from '@tanstack/table-core';
	import { SimpleDataTable } from '$components/data-table';
	import { renderSnippet } from '$lib/components/ui/data-table';
	import * as AlertDialog from '$ui/alert-dialog';
	import * as Dialog from '$ui/dialog';
	import SearchDropdown, { type ListItem } from '$components/search-dropdown';
	import { enhance } from '$app/forms';
	import FormDialog from '../form-dialog.svelte';
	import Loader from '$components/loader.svelte';

	let { data } = $props();
	const modelName = 'organizations';
	const modelTitle = 'Organization';
	const allUsers = $derived(data.userCollection.data.map((user) => user.attributes) as User[]);
	const modelResource = $derived(data.model as ApiOrgResource);
	const model = $derived(modelResource.data.attributes as Org);
	const orgUsers = $derived(
		modelResource.data.relationships?.users.map((user) => user.attributes) as User[]
	);
	const hasUsers = $derived(orgUsers.length > 0);
	const searchUsers = $derived(
		allUsers
			.filter((user) => !orgUsers.some(({ id: orgUserId }) => orgUserId === user.id))
			.map(({ id, name, email }) => ({
				id,
				label: `ID#${id} - ${name} (${email})`,
				searchValue: `${id} ${name} ${email}`
			}))
	);

	let deleteUserDialogIsOpen = $state(false);
	let deleteUserDialogRelatedId = $state(0);
	let addUserDialogIsOpen = $state(false);
	let addUserDialogSelectedList = $state<ListItem[]>([]);
	let addUserDialogIsLoading = $state(false);
	let deleteUserDialogIsLoading = $state(false);
	let deleteOrgDialogIsOpen = $state(false);
	let deleteOrgDialogIsLoading = $state(false);
	let editOrgDialogIsOpen = $state(false);

	function resetDeleteDialogIds() {
		deleteUserDialogRelatedId = 0;
	}

	function handleAddUserDialogCancel() {
		addUserDialogSelectedList = [];
		addUserDialogIsOpen = false;
	}

	async function handleAddUserDialogSubmit() {
		if (addUserDialogSelectedList.length === 0) {
			toast.error('No users selected');
			return;
		}
		try {
			addUserDialogIsLoading = true;
			const formData = new FormData();
			formData.append('userIds', addUserDialogSelectedList.map((item) => item.id).join(','));
			const response = await fetch('?/addUsers', {
				method: 'POST',
				body: formData
			});
			const result = await response.json();
			if (result.type === 'success') {
				addUserDialogSelectedList = [];
				toast.success('Users added successfully');
				invalidate('organizations:view');
				addUserDialogIsOpen = false;
			} else {
				toast.error('Failed to add users');
			}
		} catch (error) {
			toast.error('Failed to add users');
		} finally {
			addUserDialogIsLoading = false;
		}
	}

	const usersColumns: ColumnDef<User>[] = [
		{
			accessorKey: 'id',
			header: 'ID'
		},
		{
			accessorKey: 'name',
			header: 'Name'
		},
		{
			accessorKey: 'email',
			header: 'Email'
		},
		{
			id: 'actions',
			cell: ({ row }) =>
				renderSnippet(DataTableActions, { modelId: model.id, RelatedId: row.original.id })
		}
	];
</script>

<h1 class="text-2xl font-medium capitalize">{modelTitle} - #{model.id} - {model.name}</h1>
<Card.Root class="w-full">
	<Card.Header>
		<Card.Title class="text-lg">{modelTitle}</Card.Title>
		<Card.Description>View {modelTitle.toLowerCase()} details.</Card.Description>
		<Card.Action>
			<Button
				variant="outline"
				class="transition-all duration-200 hover:bg-blue-50 hover:text-blue-500"
				onclick={() => (editOrgDialogIsOpen = true)}
			>
				<Pencil class="h-4 w-4" />
				Edit
			</Button>
			<Button
				variant="outline"
				class="text-destructive border-red-200 transition-all duration-200 hover:bg-red-50 hover:text-red-500"
				onclick={() => (deleteOrgDialogIsOpen = true)}
			>
				<Trash2 class="h-4 w-4" />
				Delete
			</Button>
		</Card.Action>
	</Card.Header>
	<Card.Content>
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
				<DL.Label>Status</DL.Label>
				<DL.Content>
					<StatusBadge bind:status={model.status} class="text-sm" />
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Users Count</DL.Label>
				<DL.Content>{orgUsers.length}</DL.Content>
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

{#snippet DataTableActions({ modelId, RelatedId }: { modelId: number; RelatedId: number })}
	<div class="flex justify-end">
		<Button
			variant="outline"
			class="text-destructive border-red-200 transition-all duration-200 hover:bg-red-50 hover:text-red-500"
			onclick={() => {
				deleteUserDialogRelatedId = RelatedId;
				deleteUserDialogIsOpen = true;
			}}
		>
			<Trash2 class="h-4 w-4" />
		</Button>
	</div>
{/snippet}

<Card.Root class="w-full">
	<Card.Header>
		<Card.Title>Users</Card.Title>
		<Card.Description>View {modelTitle.toLowerCase()} users.</Card.Description>
		<Card.Action>
			<Button
				variant="outline"
				size="sm"
				class="transition-all duration-200 hover:bg-blue-50 hover:text-blue-500"
				onclick={() => (addUserDialogIsOpen = true)}
			>
				<UserPlus class="h-4 w-4" />
				Add User
			</Button>
		</Card.Action>
	</Card.Header>
	<Card.Content>
		{#if hasUsers}
			<SimpleDataTable data={orgUsers} columns={usersColumns} />
		{:else}
			<div class="flex h-full items-center justify-center">
				<p class="text-sm text-gray-500">No users found</p>
			</div>
		{/if}
	</Card.Content>
</Card.Root>

<FormDialog
	bind:isOpen={editOrgDialogIsOpen}
	{model}
	data={data.form}
	onSuccess={async () => {
		await invalidate('organizations:view');
		editOrgDialogIsOpen = false;
	}}
/>

<AlertDialog.Root bind:open={deleteOrgDialogIsOpen}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>
				{#if hasUsers}
					Unable to delete organization
				{:else}
					Are you sure?
				{/if}
			</AlertDialog.Title>
			{#if hasUsers}
				<AlertDialog.Description>
					Remove all users before deleting this organization.
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
							toast.error('Unable to delete organization');
							return cancel();
						}
						deleteOrgDialogIsLoading = true;
						return async ({ result, update }) => {
							if (result.type === 'redirect') {
								goto(result?.location || '/organizations', { invalidateAll: true }).then(() => {
									toast.success('Organization deleted successfully');
								});
								deleteOrgDialogIsLoading = false;
								deleteOrgDialogIsOpen = false;
							} else {
								toast.error('Failed to delete organization');
							}
							deleteOrgDialogIsLoading = false;
							deleteOrgDialogIsOpen = false;
						};
					}}
				>
					<AlertDialog.Cancel disabled={deleteUserDialogIsLoading} type="reset"
						>Cancel</AlertDialog.Cancel
					>
					<AlertDialog.Action disabled={deleteUserDialogIsLoading} type="submit">
						Delete
					</AlertDialog.Action>
				</form>
			{/if}
		</AlertDialog.Footer>
		{#if deleteOrgDialogIsLoading}
			<Loader show={deleteOrgDialogIsLoading} />
		{/if}
	</AlertDialog.Content>
</AlertDialog.Root>

<AlertDialog.Root
	bind:open={deleteUserDialogIsOpen}
	onOpenChange={(isOpen) => {
		if (!isOpen) {
			resetDeleteDialogIds();
		}
	}}
>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>Are you sure?</AlertDialog.Title>
		</AlertDialog.Header>
		<AlertDialog.Footer>
			<form
				method="POST"
				action="?/deleteUser"
				use:enhance={({ formElement, formData, action, cancel, submitter }) => {
					if (!deleteUserDialogRelatedId) {
						toast.error('No user selected');
						return cancel();
					}
					deleteUserDialogIsLoading = true;
					return async ({ result, update }) => {
						if (result.type === 'success') {
							toast.success('User deleted successfully');
							invalidate('organizations:view');
							cancel();
						} else {
							toast.error('Failed to remove user');
						}
						deleteUserDialogIsLoading = false;
						deleteUserDialogIsOpen = false;
						resetDeleteDialogIds();
					};
				}}
			>
				<input type="hidden" name="userIds" value={deleteUserDialogRelatedId} />
				<AlertDialog.Cancel disabled={deleteUserDialogIsLoading} type="reset"
					>Cancel</AlertDialog.Cancel
				>
				<AlertDialog.Action disabled={deleteUserDialogIsLoading} type="submit">
					Delete
				</AlertDialog.Action>
			</form>
		</AlertDialog.Footer>
		{#if deleteUserDialogIsLoading}
			<Loader show={deleteUserDialogIsLoading} />
		{/if}
	</AlertDialog.Content>
</AlertDialog.Root>

<Dialog.Root bind:open={addUserDialogIsOpen}>
	<Dialog.Content
		class="sm:max-w-xl"
		interactOutsideBehavior="ignore"
		onOpenAutoFocus={(e) => e.preventDefault()}
	>
		<Dialog.Header>
			<Dialog.Title>Add User</Dialog.Title>
			<Dialog.Description>Search users by name or email.</Dialog.Description>
		</Dialog.Header>
		<div class="relative">
			<SearchDropdown
				initialList={searchUsers}
				submitButtonLabel="Add"
				searchPlaceholder="Search users by name or email"
				bind:selectedList={addUserDialogSelectedList}
			/>
		</div>
		<Dialog.Footer>
			<Button variant="outline" onclick={handleAddUserDialogCancel}>Cancel</Button>
			<Button variant="default" onclick={handleAddUserDialogSubmit}>Add</Button>
		</Dialog.Footer>
		{#if addUserDialogIsLoading}
			<Loader show={addUserDialogIsLoading} />
		{/if}
	</Dialog.Content>
</Dialog.Root>
