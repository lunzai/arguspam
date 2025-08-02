<script lang="ts">
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Plus, Trash2, Users, SquareArrowOutUpRight } from '@lucide/svelte';
	import type { UserCollection } from '$lib/resources/user';
	import type { UserGroupCollection } from '$lib/resources/user-group';
	import * as Avatar from '$ui/avatar';
	import { generateAvatar, getInitials } from '$utils/avatar';

	interface Props {
		approverUserGroups: UserGroupCollection;
		approverUsers: UserCollection;
	}

	interface ListItem {
		id: number;
		name: string;
		email: string | null;
		description: string | null;
		isGroup: boolean;
	}

	let { approverUserGroups, approverUsers }: Props = $props();

	const list: ListItem[] = $derived([
		...approverUserGroups.map((row) => {
			return {
				id: row.attributes.id,
				name: row.attributes.name,
				email: null,
				description: row.attributes.description,
				isGroup: true
			};
		}),
		...approverUsers.map((row) => {
			return {
				id: row.attributes.id,
				name: row.attributes.name,
				email: row.attributes.email,
				description: null,
				isGroup: false
			};
		})
	]);

	function handleDelete(row: ListItem) {
		console.log(row);
	}
</script>

<Card.Root class="w-full">
	<Card.Header>
		<Card.Title class="text-lg">Approvers</Card.Title>
		<Card.Description>Asset's approvers.</Card.Description>
		<Card.Action>
			<Button
				variant="outline"
				class="transition-all duration-200 hover:bg-blue-50 hover:text-blue-500"
			>
				<Plus class="h-4 w-4" />
				Add Approver
			</Button>
		</Card.Action>
	</Card.Header>
	<Card.Content>
		<div class="flex flex-col gap-6">
			{#each list as item}
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
					<div class="flex flex-1 flex-col gap-0.5">
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
						<Button
							variant="outline"
							class="text-destructive border-red-200 transition-all duration-200 hover:bg-red-50 hover:text-red-500"
							onclick={() => {
								handleDelete(item);
							}}
						>
							<Trash2 class="h-4 w-4" />
						</Button>
					</div>
				</div>
			{:else}
				<div class="flex h-full items-center justify-center">
					<p class="text-sm text-gray-500">No users found</p>
				</div>
			{/each}
		</div>
	</Card.Content>
</Card.Root>
