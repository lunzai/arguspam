<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Plus, Trash2, Users, SquareArrowOutUpRight } from '@lucide/svelte';
	import type { UserCollection } from '$lib/resources/user';
	import type { UserGroupCollection } from '$lib/resources/user-group';
	import * as Avatar from '$ui/avatar';
	import { generateAvatar, getInitials } from '$utils/avatar';
	import * as AlertDialog from '$ui/alert-dialog';
	import Loader from '$components/loader.svelte';
	import { enhance } from '$app/forms';
	import { toast } from 'svelte-sonner';
	import { invalidate } from '$app/navigation';
	import { capitalizeWords } from '$utils/string';
	import * as Dialog from '$ui/dialog';
	import SearchDropdown, { type ListItem } from '$components/search-dropdown';

	interface Props {
		currentUserGroups: UserGroupCollection;
		currentUsers: UserCollection;
		allUserGroups: UserGroupCollection;
		allUsers: UserCollection;
		role: string;
		rolePural: string;
		canAddAccessGrant: boolean;
		canRemoveAccessGrant: boolean;
	}

	interface RowItem {
		id: number;
		name: string;
		email: string | null;
		description: string | null;
		isGroup: boolean;
	}

	let {
		currentUserGroups = $bindable(),
		currentUsers = $bindable(),
		allUserGroups = $bindable(),
		allUsers = $bindable(),
		role = '',
		rolePural = '',
		canAddAccessGrant = false,
		canRemoveAccessGrant = false
	}: Props = $props();

	let addDialogIsOpen = $state(false);
	let addDialogIsLoading = $state(false);
	let addDialogSelectedList = $state<ListItem[]>([]);

	let deleteDialogIsOpen = $state(false);
	let deleteDialogIsLoading = $state(false);
	let deleteRow: RowItem | null = $state(null);

	const searchList = $derived(
		allUserGroups
			.filter(
				({ attributes: { id: checkId } }) =>
					!currentUserGroups.some(({ attributes: { id: compareId } }) => compareId === checkId)
			)
			.map(({ attributes: { id, name } }) => ({
				id: `group|${id}`,
				label: `GID#${id} - ${name} (Group)`,
				searchValue: `${id} ${name} Group`
			}))
			.concat(
				allUsers
					.filter(
						({ attributes: { id: checkId } }) =>
							!currentUsers.some(({ attributes: { id: compareId } }) => compareId === checkId)
					)
					.map(({ attributes: { id, name, email } }) => ({
						id: `user|${id}`,
						label: `UID#${id} - ${name} (${email})`,
						searchValue: `${id} ${name} ${email}`
					}))
			)
	);

	const rowList: RowItem[] = $derived([
		...currentUserGroups.map((row) => {
			return {
				id: row.attributes.id,
				name: row.attributes.name,
				email: null,
				description: row.attributes.description,
				isGroup: true
			};
		}),
		...currentUsers.map((row) => {
			return {
				id: row.attributes.id,
				name: row.attributes.name,
				email: row.attributes.email,
				description: null,
				isGroup: false
			};
		})
	]);

	function handleDelete(row: RowItem) {
		deleteRow = row;
		deleteDialogIsOpen = true;
	}

	function handleDeleteOpenChange(open: boolean) {
		if (!open) {
			deleteRow = null;
		}
	}

	function handleAddDialogCancel() {
		addDialogSelectedList = [];
		addDialogIsOpen = false;
	}

	async function handleAddDialogSubmit() {
		if (addDialogSelectedList.length === 0) {
			toast.error('No users or groups selected');
			return;
		}
		try {
			addDialogIsLoading = true;
			const formData = new FormData();
			const userIds = addDialogSelectedList
				.filter((item) => item.id.startsWith('user|'))
				.map((item) => item.id.split('|')[1]);
			const userGroupIds = addDialogSelectedList
				.filter((item) => item.id.startsWith('group|'))
				.map((item) => item.id.split('|')[1]);
			formData.append('userIds', userIds.join(','));
			formData.append('groupIds', userGroupIds.join(','));
			formData.append('role', role);
			const response = await fetch('?/addAccess', {
				method: 'POST',
				body: formData
			});
			const result = await response.json();
			if (result.type === 'success') {
				addDialogSelectedList = [];
				invalidate('asset:view');
				toast.success('Access granted successfully');
				addDialogIsOpen = false;
			} else {
				toast.error('Failed to grant access');
			}
		} catch (error) {
			toast.error('Failed to grant access');
		} finally {
			addDialogIsLoading = false;
		}
	}
</script>

<Card.Root class="w-full">
	<Card.Header>
		<Card.Title class="text-lg">{capitalizeWords(rolePural)}</Card.Title>
		<Card.Description>Asset's {rolePural}.</Card.Description>
		<Card.Action>
			{#if canAddAccessGrant}
				<Button
					variant="outline"
					class="transition-all duration-200 hover:bg-blue-50 hover:text-blue-500"
					onclick={() => {
						addDialogIsOpen = true;
					}}
				>
					<Plus class="h-4 w-4" />
					Add {capitalizeWords(role)}
				</Button>
			{/if}
		</Card.Action>
	</Card.Header>
	<Card.Content>
		<div class="flex flex-col gap-6">
			{#each rowList as item}
				<div class="flex items-center gap-4">
					<Avatar.Root class="size-8 rounded-lg">
						{#if item.email}
							<Avatar.Image src={generateAvatar(`${item.id}|${item.email}`)} alt={item.name} />
							<Avatar.Fallback class="rounded-lg">{getInitials(item.name)}</Avatar.Fallback>
						{:else}
							<Avatar.Fallback class="rounded-lg">
								<Users class="size-4" />
							</Avatar.Fallback>
						{/if}
					</Avatar.Root>
					<div class="flex flex-1 flex-col gap-0.5 truncate">
						<span class="truncate font-medium">{item.name}</span>
						<span class="text-muted-foreground truncate text-xs">
							{item.isGroup ? 'User Group' : 'User'} #{item.id}
							{item.email ? `| ${item.email}` : ''}{item.description ? `| ${item.description}` : ''}
						</span>
					</div>
					<div class="flex items-center gap-2">
						<Button
							variant="outline"
							class="transition-all duration-200"
							href={item.isGroup ? `/organizations/user-groups/${item.id}` : `/users/${item.id}`}
							target="_blank"
						>
							<SquareArrowOutUpRight class="h-4 w-4" />
						</Button>
						{#if canRemoveAccessGrant}
							<Button
								variant="outline"
								class="text-destructive border-red-200 transition-all duration-200 hover:bg-red-50 hover:text-red-500"
								onclick={() => {
									handleDelete(item);
								}}
							>
								<Trash2 class="h-4 w-4" />
							</Button>
						{/if}
					</div>
				</div>
			{:else}
				<div class="flex h-full items-center justify-center">
					<p class="text-sm text-gray-500">No {rolePural} found</p>
				</div>
			{/each}
		</div>
	</Card.Content>
</Card.Root>

<AlertDialog.Root bind:open={deleteDialogIsOpen} onOpenChangeComplete={handleDeleteOpenChange}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>Are you sure?</AlertDialog.Title>
			<AlertDialog.Description>
				Are you sure you want to remove
				<span class="font-semibold">
					{deleteRow?.name} ({deleteRow?.isGroup ? 'User Group' : 'User'})
				</span>
				as the {role}?
			</AlertDialog.Description>
		</AlertDialog.Header>
		<AlertDialog.Footer>
			<AlertDialog.Cancel>Cancel</AlertDialog.Cancel>
			<form
				method="POST"
				action="?/removeAccess"
				use:enhance={({ cancel }) => {
					deleteDialogIsLoading = true;
					return async ({ result, update }) => {
						if (result.type === 'success') {
							toast.success(`${capitalizeWords(role)} removed successfully`);
							await invalidate('asset:view');
							deleteDialogIsLoading = false;
							deleteDialogIsOpen = false;
						} else {
							toast.error(`Failed to remove ${role}`);
						}
						deleteDialogIsLoading = false;
						deleteDialogIsOpen = false;
					};
				}}
			>
				<input type="text" name="id" value={deleteRow?.id} hidden />
				<input type="text" name="role" value={role} hidden />
				<input type="text" name="type" value={deleteRow?.isGroup ? 'user_group' : 'user'} hidden />
				<AlertDialog.Action type="submit">Remove</AlertDialog.Action>
			</form>
		</AlertDialog.Footer>
		<Loader show={deleteDialogIsLoading} />
	</AlertDialog.Content>
</AlertDialog.Root>

<Dialog.Root bind:open={addDialogIsOpen}>
	<Dialog.Content
		class="max-h-[90vh] sm:max-w-xl"
		interactOutsideBehavior="ignore"
		onOpenAutoFocus={(e) => e.preventDefault()}
	>
		<Dialog.Header>
			<Dialog.Title>Add {capitalizeWords(role)}</Dialog.Title>
			<Dialog.Description>Search users or groups by name or email.</Dialog.Description>
		</Dialog.Header>
		<div class="relative">
			<SearchDropdown
				initialList={searchList}
				submitButtonLabel="Add"
				searchPlaceholder="Search users or groups by name or email"
				bind:selectedList={addDialogSelectedList}
			/>
		</div>
		<Dialog.Footer>
			<Button variant="outline" onclick={handleAddDialogCancel}>Cancel</Button>
			<Button variant="default" onclick={handleAddDialogSubmit}>Add</Button>
		</Dialog.Footer>
		{#if addDialogIsLoading}
			<Loader show={addDialogIsLoading} />
		{/if}
	</Dialog.Content>
</Dialog.Root>
