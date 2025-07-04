<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Loader2, Pencil, Trash2, UserPlus } from '@lucide/svelte';
	import { Separator } from '$ui/separator';
	import * as DL from '$components/description-list';
	import { relativeDateTime } from '$utils/date';
	import { StatusBadge } from '$components/badge';
	import type { ResourceItem } from '$resources/api';
	import type { UserGroupResource } from '$lib/resources/user-group';
	import type { UserGroup } from '$models/user-group';
	import type { User } from '$models/user';
	import type { ColumnDef } from '@tanstack/table-core';
	import { SimpleDataTable } from '$components/data-table';
	import { renderSnippet } from '$lib/components/ui/data-table';
	import * as AlertDialog from '$ui/alert-dialog';
	import * as Dialog from '$ui/dialog';
	import { toast } from 'svelte-sonner';
	import { invalidate, goto } from '$app/navigation';
	import SearchDropdown, { type ListItem } from '$components/search-dropdown';
	import type { UserCollection, UserResource } from '$resources/user';
	import { enhance } from '$app/forms';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import { UserGroupSchema } from '$validations/user-group';
	import { Input } from '$ui/input';
	import * as Select from '$ui/select';
	import { Textarea } from '$ui/textarea';
	import * as Form from '$ui/form';
	import { capitalizeWords } from '$utils/string';

	let { data } = $props();
	const modelName = 'user-groups';
	const modelTitle = 'User Group';
	const userCollection = $derived(data.userCollection as UserCollection);
	const modelResource = $derived(data.model as UserGroupResource);
	const model = $derived(modelResource.data.attributes as UserGroup);
	const groupUsers = $derived(modelResource.data.relationships?.users as ResourceItem<User>[]);
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

	const editUserGroupForm = superForm(data.form, {
		validators: zodClient(UserGroupSchema),
		delayMs: 100,
		onUpdate({ form, result }) {
			if (!form.valid) {
				return;
			}
			if (result.type === 'success') {
				toast.success(result.data.message);
				invalidate('user-groups:data');
				editUserGroupDialogIsOpen = false;
			} else if (result.type === 'failure') {
				toast.error(result.data.error);
			}
		}
	});

	const {
		form: editUserGroupFormData,
		enhance: editUserGroupDialogEnhance,
		submitting: editUserGroupDialogSubmitting,
		delayed: editUserGroupDialogDelayed
	} = editUserGroupForm;

	function handleEditUserGroupDialogCancel() {
		editUserGroupDialogIsOpen = false;
	}

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
				invalidate('user-groups:data');
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

<h1 class="text-2xl font-medium capitalize">{modelTitle} - #{model.id} - {model.name}</h1>
<Card.Root class="gap-3 rounded-lg py-3 shadow-none">
	<Card.Header class="flex items-center justify-between px-3">
		<Card.Title>{modelTitle} Details</Card.Title>
		<Card.Action>
			<Button
				variant="outline"
				size="sm"
				class="transition-all duration-200 hover:bg-blue-50 hover:text-blue-500"
				onclick={() => (editUserGroupDialogIsOpen = true)}
			>
				<Pencil class="h-4 w-4" />
				Edit
			</Button>
			<Button
				variant="outline"
				size="sm"
				class="text-destructive border-red-200 transition-all duration-200 hover:bg-red-50 hover:text-red-500"
				onclick={() => (deleteUserGroupDialogIsOpen = true)}
			>
				<Trash2 class="h-4 w-4" />
				Delete
			</Button>
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
				<DL.Content>{model.description}</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Status</DL.Label>
				<DL.Content>
					<StatusBadge status={model.status} class="text-sm" />
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Users Count</DL.Label>
				<DL.Content>{groupUsers.length}</DL.Content>
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

<Card.Root class="gap-3 rounded-lg py-3 shadow-none">
	<Card.Header class="flex items-center justify-between px-3">
		<Card.Title>Users</Card.Title>
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
	<Separator />
	<Card.Content class="gap-3 px-3">
		{#if hasUsers}
			<SimpleDataTable data={groupUsers.map((user) => user.attributes)} columns={usersColumns} />
		{:else}
			<div class="flex h-full items-center justify-center">
				<p class="text-sm text-gray-500">No users found</p>
			</div>
		{/if}
	</Card.Content>
</Card.Root>

<Dialog.Root bind:open={editUserGroupDialogIsOpen}>
	<Dialog.Content class="sm:max-w-2xl" interactOutsideBehavior="ignore">
		<form class="space-y-6" method="POST" action="?/save" use:editUserGroupDialogEnhance>
			<input type="hidden" name="id" value={model.id} />
			<input type="hidden" name="org_id" value={model.org_id} />
			<Dialog.Header>
				<Dialog.Title>Edit User Group</Dialog.Title>
				<Dialog.Description>Edit user group details.</Dialog.Description>
			</Dialog.Header>
			<div class="space-y-6">
				<Form.Field form={editUserGroupForm} name="name">
					<Form.Control>
						<Form.Label>Name</Form.Label>
						<Input
							type="text"
							name="name"
							bind:value={$editUserGroupFormData.name}
							disabled={$editUserGroupDialogSubmitting}
						/>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>
				<Form.Field form={editUserGroupForm} name="description">
					<Form.Control>
						<Form.Label>Description</Form.Label>
						<Textarea
							name="description"
							bind:value={$editUserGroupFormData.description}
							disabled={$editUserGroupDialogSubmitting}
							class="min-h-30"
						/>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>
				<Form.Field form={editUserGroupForm} name="status">
					<Form.Control>
						<Form.Label>Status</Form.Label>
						<Select.Root
							name="status"
							type="single"
							bind:value={$editUserGroupFormData.status}
							disabled={$editUserGroupDialogSubmitting}
						>
							<Select.Trigger class="w-64">
								{$editUserGroupFormData.status
									? capitalizeWords($editUserGroupFormData.status)
									: 'Select status'}
							</Select.Trigger>
							<Select.Content>
								<Select.Item value="active" label="Active" />
								<Select.Item value="inactive" label="Inactive" />
							</Select.Content>
						</Select.Root>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>
			</div>
			<Dialog.Footer>
				<Button variant="outline" onclick={handleEditUserGroupDialogCancel}>Cancel</Button>
				<Button variant="default" type="submit">Save</Button>
			</Dialog.Footer>
			{#if $editUserGroupDialogSubmitting}
				<div
					class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-gray-50/50 transition-all"
				>
					<Loader2 class="h-8 w-8 animate-spin text-gray-300" />
				</div>
			{/if}
		</form>
	</Dialog.Content>
</Dialog.Root>

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
								goto(result?.location || '/user-groups', { invalidateAll: true }).then(() => {
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
					<input type="hidden" name="userIds" value={deleteUserDialogRelatedId} />
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
			<div
				class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-gray-50/50 transition-all"
			>
				<Loader2 class="h-8 w-8 animate-spin text-gray-300" />
			</div>
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
							invalidate('user-groups:data');
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
			<div
				class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-gray-50/50 transition-all"
			>
				<Loader2 class="h-8 w-8 animate-spin text-gray-300" />
			</div>
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
			<div
				class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-gray-50/50 transition-all"
			>
				<Loader2 class="h-8 w-8 animate-spin text-gray-300" />
			</div>
		{/if}
	</Dialog.Content>
</Dialog.Root>
