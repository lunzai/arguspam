<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2, UserPlus } from '@lucide/svelte';
	import { Separator } from '$ui/separator';
	import * as DL from '$components/description-list';
	import { relativeDateTime } from '$utils/date';
	import { StatusBadge } from '$components/badge';
	import type { ResourceItem } from '$resources/api';
	import type { UserGroupResource } from '$lib/resources/user-group';
	import type { UserGroup } from '$models/user-group';
	import type { User } from '$models/user';
	import type { ColumnDef } from "@tanstack/table-core";
	import { SimpleDataTable } from '$components/data-table';
	import { renderSnippet } from '$lib/components/ui/data-table';
	import * as AlertDialog from '$ui/alert-dialog';
	import * as Dialog from '$ui/dialog';
	import { Input } from '$ui/input';

	let { data } = $props();
	const modelResource = $derived(data.model as UserGroupResource);
	const model = $derived(modelResource.data.attributes as UserGroup);
	const users = $derived(modelResource.data.relationships?.users as ResourceItem<User>[]);
	const modelName = 'user-groups';
	const modelTitle = 'User Group';
	let deleteDialogIsOpen = $state(false);
	let deleteDialogModelId = $state(0);
	let deleteDialogRelatedId = $state(0);
	let addUserDialogIsOpen = $state(false);

	function onConfirmDelete(modelId: number, RelatedId: number) {
		console.log('delete', modelId, RelatedId);
		deleteDialogIsOpen = false;
		resetDeleteDialogIds();
	}

	function resetDeleteDialogIds() {
		deleteDialogModelId = 0;
		deleteDialogRelatedId = 0;
	}

	const usersColumns: ColumnDef<User>[] = [
		{
			accessorKey: 'id',
			header: 'ID',
		},
		{
			accessorKey: 'name',
			header: 'Name',
		},
		{
			accessorKey: 'email',
			header: 'Email',
		},
		// {
		// 	id: 'actions',
		// 	cell: ({ row }) => {
		// 		return renderComponent(SimpleDataTableDeleteAction, {
		// 			onConfirm: (setOpen) => {
		// 				console.log('delete', model.id, row.original.id);
		// 				setOpen(false);
		// 			}
		// 		});
		// 	},
		// }
		{
			id: "actions",
			cell: ({ row }) => renderSnippet(DataTableActions, { modelId : model.id, RelatedId : row.original.id }),
		},
	];
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
				href={`/${modelName}/${model.id}/edit`}
			>
				<Pencil class="h-4 w-4" />
				Edit
			</Button>
			<Button
				variant="outline"
				size="sm"
				class="text-destructive border-red-200 transition-all duration-200 hover:bg-red-50 hover:text-red-500"
				href={`/${modelName}/${model.id}/delete`}
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
				<DL.Label>Status</DL.Label>
				<DL.Content>
					<StatusBadge status={model.status} class="text-sm" />
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Users Count</DL.Label>
				<DL.Content>{users.length}</DL.Content>
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

{#if users.length > 0}
	<Card.Root class="gap-3 rounded-lg py-3 shadow-none">
		<Card.Header class="flex items-center justify-between px-3">
			<Card.Title>Users</Card.Title>
			<Card.Action>
				<Button
					variant="outline"
					size="sm"
					class="transition-all duration-200 hover:bg-blue-50 hover:text-blue-500"
					onclick={() => addUserDialogIsOpen = true}
				>
					<UserPlus class="h-4 w-4" />
					Add User
				</Button>
			</Card.Action>
		</Card.Header>
		<Separator />
		<Card.Content class="gap-3 px-3">
			<SimpleDataTable data={users.map(user => user.attributes)} columns={usersColumns} />
		</Card.Content>
	</Card.Root>
{/if}

{#snippet DataTableActions({ modelId, RelatedId }: { modelId: number, RelatedId: number })}
	<div class="flex  justify-end">
		<Button 
			variant="outline" 
			class='text-destructive border-red-200 transition-all duration-200 hover:bg-red-50 hover:text-red-500'
			onclick={() => {
				deleteDialogModelId = modelId;
				deleteDialogRelatedId = RelatedId;
				deleteDialogIsOpen = true;
			}}
		>
			<Trash2 class='h-4 w-4' />
		</Button>
	</div>
{/snippet}


<AlertDialog.Root bind:open={deleteDialogIsOpen} onOpenChange={(isOpen) => {
	if (!isOpen) {
		resetDeleteDialogIds();
	}
}}>
	<AlertDialog.Content>
		<AlertDialog.Header>
			<AlertDialog.Title>
				Are you sure?
			</AlertDialog.Title>
		</AlertDialog.Header>
		<AlertDialog.Footer>
			<AlertDialog.Cancel>
				Cancel
			</AlertDialog.Cancel>
			<AlertDialog.Action onclick={() => onConfirmDelete(deleteDialogModelId, deleteDialogRelatedId)}>
				Delete
			</AlertDialog.Action>
		</AlertDialog.Footer>
	</AlertDialog.Content>
</AlertDialog.Root>

<Dialog.Root bind:open={addUserDialogIsOpen}>
	<Dialog.Content class="sm:max-w-xl">
		<Dialog.Header>
			<Dialog.Title>Add User</Dialog.Title>
			<Dialog.Description>
				Search users by name or email.
			</Dialog.Description>
		</Dialog.Header>
		<div class="grid gap-4 py-4">
			<Input id="name" value="Pedro Duarte" class="col-span-3" />
		</div>
		<Dialog.Footer>
			<Button variant="outline" onclick={() => addUserDialogIsOpen = false}>Cancel</Button>
			<Button variant="outline" onclick={() => addUserDialogIsOpen = false}>Add</Button>
		</Dialog.Footer>
	</Dialog.Content>
</Dialog.Root>
