<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2, UserPlus } from '@lucide/svelte';
	import * as DL from '$components/description-list';
	import { relativeDateTime } from '$utils/date';
	import { StatusBadge } from '$components/badge';
	import type { ApiUserGroupResource } from '$resources/user-group';
	import type { UserResource } from '$resources/user';
	import type { UserGroup } from '$models/user-group';
	import type { User } from '$models/user';
	import type { ColumnDef } from '@tanstack/table-core';
	import { SimpleDataTable } from '$components/data-table';
	import { renderSnippet } from '$ui/data-table';
	import * as AlertDialog from '$ui/alert-dialog';
	import * as Dialog from '$ui/dialog';
	import { toast } from 'svelte-sonner';
	import { invalidate, goto } from '$app/navigation';
	import SearchDropdown, { type ListItem } from '$components/search-dropdown';
	import type { ApiUserCollection } from '$resources/user';
	import { enhance } from '$app/forms';
	import FormDialog from '../form-dialog.svelte';
	import Loader from '$components/loader.svelte';
	import { PageTitle } from '$components/page-title';
	import * as ButtonGroup from '$ui/button-group';

	let { data } = $props();
	const canUpdate = $derived(data.canUpdate);
	const canDelete = $derived(data.canDelete);
	const canAddUser = $derived(data.canAddUser);
	const canRemoveUser = $derived(data.canRemoveUser);
	const userCollection = $derived(data.userCollection as ApiUserCollection);
	const modelResource = $derived(data.model as ApiUserGroupResource);
	const model = $derived(modelResource.data.attributes as UserGroup);
	const groupUsers = $derived(modelResource.data.relationships?.users as UserResource[]);
	const hasUsers = $derived(groupUsers.length > 0);
	let deleteUserDialogIsOpen = $state(false);
	let deleteUserDialogRelatedId = $state(0);
	let addUserDialogIsOpen = $state(false);
	let addUserDialogSelectedList = $state<ListItem[]>([]);
	let addUserDialogIsLoading = $state(false);
	let deleteUserDialogIsLoading = $state(false);
	let deleteUserGroupDialogIsOpen = $state(false);
	let deleteUserGroupDialogIsLoading = $state(false);
	let editUserGroupDialogIsOpen = $state(false);

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
				invalidate('user-groups:view');
				toast.success('Users added successfully');
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
		}
	];
	if (canRemoveUser) {
		usersColumns.push({
			id: 'actions',
			cell: ({ row }) =>
				renderSnippet(DataTableActions, { modelId: model.id, RelatedId: row.original.id })
		});
	}

	const userList = $derived(
		userCollection.data
			.filter(
				({ attributes: { id } }) =>
					!groupUsers.some(({ attributes: { id: groupUserId } }) => groupUserId === id)
			)
			.map(({ attributes: { id, name, email } }) => ({
				id,
				label: `ID#${id} - ${name} (${email})`,
				searchValue: `${id} ${name} ${email}`
			}))
	);
</script>

<div class="flex flex-row space-x-2 items-center">
    <StatusBadge status={model.status} />
    <div class="text-sm text-slate-500 uppercase tracking-wider font-semibold">
        USER GROUP #{model.id}
    </div>
</div>
<PageTitle
    title={model.name}
    class="-mt-4"
>
    <div class="flex justify-between gap-2">
        <ButtonGroup.Root>
            {#if canUpdate}
                <Button
                    variant="outline"
                    class="transition-all duration-200 h-auto py-2.5 px-4! bg-white hover:border-blue-200 hover:bg-blue-50 hover:text-blue-500"
                    onclick={() => (editUserGroupDialogIsOpen = true)}
                >
                    <Pencil class="h-4 w-4" />
                    Edit
                </Button>
            {/if}
            {#if canDelete}
                <Button
                    variant="outline"
                    class="transition-all duration-200 h-auto py-2.5 px-4! bg-white hover:border-red-200 hover:bg-red-50 hover:text-red-500"
                    onclick={() => (deleteUserGroupDialogIsOpen = true)}
                >
                    <Trash2 class="h-4 w-4" />
                    Delete
                </Button>
            {/if}
        </ButtonGroup.Root>
    </div>
</PageTitle>

<div class="flex flex-col space-y-4">
    <div class="mt-2 flex flex-col gap-6 lg:flex-row"> 
        <aside class="flex w-full flex-col gap-6 lg:w-80">
            <Card.Root class="w-full border-0 shadow-xs">
                <Card.Header>
                    <Card.Title class="text-xl font-bold tracking-tight">User Group Details</Card.Title>
                </Card.Header>
                <Card.Content class="relative">
                    <DL.Root divider={null} dlClass="space-y-4">
                        <DL.Row orientation="vertical">
                            <DL.Label>Name</DL.Label>
                            <DL.Content>{model.name}</DL.Content>
                        </DL.Row>
                        <DL.Row orientation="vertical">
                            <DL.Label>Description</DL.Label>
                            <DL.Content>{model.description}</DL.Content>
                        </DL.Row>
                        <DL.Row orientation="vertical">
                            <DL.Label>Status</DL.Label>
                            <DL.Content>
                                <StatusBadge bind:status={model.status} class="text-sm" />
                            </DL.Content>
                        </DL.Row>
                        <DL.Row orientation="vertical">
                            <DL.Label>Users Count</DL.Label>
                            <DL.Content>{groupUsers.length}</DL.Content>
                        </DL.Row>
                        <DL.Row orientation="vertical">
                            <DL.Label>Created At</DL.Label>
                            <DL.Content>
                                {relativeDateTime(model.created_at)}
                            </DL.Content>
                        </DL.Row>
                        <DL.Row orientation="vertical">
                            <DL.Label>Updated At</DL.Label>
                            <DL.Content>
                                {relativeDateTime(model.updated_at)}
                            </DL.Content>
                        </DL.Row>
                    </DL.Root>
                </Card.Content>
            </Card.Root>
        </aside>
        <div class="min-w-0 flex-1 flex flex-col space-y-6">
            <Card.Root class="w-full border-0 shadow-xs">
                <Card.Header>
                    <Card.Title>Members</Card.Title>
                    <Card.Description>Add or remove members from this user group.</Card.Description>
                    <Card.Action>
                        {#if canAddUser}
                            <Button
                                variant="outline"
                                size="sm"
                                class="transition-all duration-200 hover:bg-blue-50 hover:text-blue-500 bg-white"
                                onclick={() => (addUserDialogIsOpen = true)}
                            >
                                <UserPlus class="h-4 w-4" />
                                Add User
                            </Button>
                        {/if}
                    </Card.Action>
                </Card.Header>
                <Card.Content>
                    {#if hasUsers}
                        <SimpleDataTable data={groupUsers.map((user) => user.attributes)} columns={usersColumns} />
                    {:else}
                        <div class="flex h-full items-center justify-center">
                            <p class="text-sm text-gray-500">No users found</p>
                        </div>
                    {/if}
                </Card.Content>
            </Card.Root>
        </div>
    </div>
</div>

<FormDialog
	bind:isOpen={editUserGroupDialogIsOpen}
	{model}
	data={data.form}
	onSuccess={async () => {
		editUserGroupDialogIsOpen = false;
	}}
/>

{#snippet DataTableActions({ modelId, RelatedId }: { modelId: number; RelatedId: number })}
	<div class="flex justify-end">
		<Button
			variant="outline"
			class="text-destructive bg-white border-red-200 transition-all duration-200 hover:bg-red-50 hover:text-red-500"
			onclick={() => {
				deleteUserDialogRelatedId = RelatedId;
				deleteUserDialogIsOpen = true;
			}}
		>
			<Trash2 class="h-4 w-4" />
		</Button>
	</div>
{/snippet}

<AlertDialog.Root bind:open={deleteUserGroupDialogIsOpen}>
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
						deleteUserGroupDialogIsLoading = true;
						return async ({ result, update }) => {
							if (result.type === 'redirect') {
								goto(result?.location || '/organizations/user-groups', {
									invalidateAll: true
								}).then(() => {
									toast.success('User group deleted successfully');
								});
								deleteUserGroupDialogIsLoading = false;
								deleteUserGroupDialogIsOpen = false;
							} else {
								toast.error('Failed to delete user group');
							}
							deleteUserGroupDialogIsLoading = false;
							deleteUserGroupDialogIsOpen = false;
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
		{#if deleteUserGroupDialogIsLoading}
			<Loader show={deleteUserGroupDialogIsLoading} />
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
							invalidate('user-groups:view');
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
		class="max-h-[90vh] sm:max-w-xl"
		interactOutsideBehavior="ignore"
		onOpenAutoFocus={(e) => e.preventDefault()}
	>
		<Dialog.Header>
			<Dialog.Title>Add User</Dialog.Title>
			<Dialog.Description>Search users by name or email.</Dialog.Description>
		</Dialog.Header>
		<div class="relative">
			<SearchDropdown
				initialList={userList}
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
