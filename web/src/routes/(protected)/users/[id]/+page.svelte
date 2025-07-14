<script lang="ts">
	import type { UserResource } from '$lib/resources/user.js';
	import type { User } from '$models/user';
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2, MailCheck, ShieldOff, ShieldCheck, ShieldAlert, MoreHorizontal, SquareAsterisk, UserLock, UserRoundX, UserRoundPen, Mail, ContactRound } from '@lucide/svelte';
	import { Separator } from '$ui/separator';
	import * as DL from '$components/description-list';
	import { relativeDateTime } from '$utils/date';
	import { StatusBadge, RedBadge, GreenBadge, YellowBadge } from '$components/badge';
	import type { ResourceItem } from '$resources/api';
	import type { Org } from '$models/org';
	import { Badge } from '$ui/badge';
	import type { UserGroup } from '$models/user-group';
	import type { Role } from '$models/role';
	import * as DropdownMenu from '$ui/dropdown-menu';
	import { page } from '$app/state';
	import * as Dialog from '$ui/dialog';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import * as Form from '$ui/form';
	import { Input } from '$ui/input';
	import { toast } from 'svelte-sonner';
	import FormDialog from './form-dialog.svelte';
	import { goto, invalidate } from '$app/navigation';
	import * as Select from '$ui/select';
	import { capitalizeWords } from '$utils/string';
	import RoleFormDialog from './role-form-dialog.svelte';

	let { data } = $props();
	const modelResource = $derived(data.model as UserResource);
	const model = $derived(modelResource.data.attributes as User);
	const orgs = $derived(modelResource.data.relationships?.orgs as ResourceItem<Org>[]);
	const roles = $derived(data.roles as ResourceItem<Role>[]);
	const userGroups = $derived(
		modelResource.data.relationships?.userGroups as ResourceItem<UserGroup>[]
	);
	const userRoles = $derived(
		modelResource.data.relationships?.roles as ResourceItem<Role>[]
	);
	let editUserDialogIsOpen = $state(false);
	let updateRolesDialogIsOpen = $state(false);
</script>
	
<Card.Root class="w-full">
	<Card.Header>
	 	<Card.Title class="text-lg">Profile</Card.Title>
		<Card.Description>View user profile.</Card.Description>
		<Card.Action>
	  		<DropdownMenu.Root >
				<DropdownMenu.Trigger>
					<Button variant="outline">
						<MoreHorizontal class="h-4 w-4" />
					</Button>
				</DropdownMenu.Trigger>
				<DropdownMenu.Content align="end">
					<DropdownMenu.Group>
						<!-- <DropdownMenu.Item>
							<Mail class="h-4 w-4" />
							Change Email
						</DropdownMenu.Item> -->
						<DropdownMenu.Item onclick={() => editUserDialogIsOpen = true}>
							<UserRoundPen class="h-4 w-4" />
							Edit User
						</DropdownMenu.Item>
						<DropdownMenu.Item onclick={() => updateRolesDialogIsOpen = true}>
							<ContactRound class="h-4 w-4" />
							Update Roles
						</DropdownMenu.Item>
						<!-- <DropdownMenu.Item>
							<UserLock class="h-4 w-4" />
							Deactivate User
						</DropdownMenu.Item> -->
					</DropdownMenu.Group>
					<DropdownMenu.Separator />
					<DropdownMenu.Group>
						<DropdownMenu.Item>
							<UserRoundX class="h-4 w-4 text-destructive" />
							<span class="text-destructive">Delete User</span>
						</DropdownMenu.Item>
					</DropdownMenu.Group>
				</DropdownMenu.Content>
			</DropdownMenu.Root>
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
				<DL.Label>Email</DL.Label>
				<DL.Content>
					<div class="flex items-center gap-2">
						{model.email}
						{#if model.email_verified_at}
							<GreenBadge>
								<MailCheck class="h-4 w-4" />
								Verified
							</GreenBadge>
						{/if}
					</div>
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>2FA</DL.Label>
				<DL.Content>
					<div class="flex items-center gap-2">
						{#if model.two_factor_enabled}
							{#if model.two_factor_confirmed_at}
								<GreenBadge class="text-sm">
									<ShieldCheck class="h-4 w-4" />
									Enrolled
								</GreenBadge>
							{:else}
								<YellowBadge class="text-sm">
									<ShieldAlert class="h-4 w-4" />
									Pending Enrollment
								</YellowBadge>
							{/if}
						{:else}
							<RedBadge class="text-sm">
								<ShieldOff class="h-4 w-4" />
								Not Enabled
							</RedBadge>
						{/if}
					</div>
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Organizations</DL.Label>
				<DL.Content>
					<div class="flex flex-wrap gap-1">
						{#each orgs as org}
							<Badge variant="outline" class="text-sm">
								{org.attributes.name}
							</Badge>
						{/each}
					</div>
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>User Groups</DL.Label>
				<DL.Content>
					<div class="flex flex-wrap gap-1">
						{#each userGroups as userGroup}
							<Badge variant="outline" class="text-sm">
								{userGroup.attributes.name}
							</Badge>
						{/each}
					</div>
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Roles</DL.Label>
				<DL.Content>
					<div class="flex flex-wrap gap-1">
						{#each userRoles as role}
							<Badge variant="outline" class="text-sm">
								{role.attributes.name}
							</Badge>
						{/each}
					</div>
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Status</DL.Label>
				<DL.Content>
					<StatusBadge bind:status={model.status} class="text-sm" />
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Last Login</DL.Label>
				<DL.Content>
					{relativeDateTime(model.last_login_at)}
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

<FormDialog
	bind:isOpen={editUserDialogIsOpen}
	model={model}
	data={data.form}
	onSuccess={async (data: User) => {
		console.log('onSuccess', data);
		editUserDialogIsOpen = false;
	}}
/>

<RoleFormDialog
	bind:isOpen={updateRolesDialogIsOpen}
	roles={roles}
	bind:data={data.updateRolesForm}
	onSuccess={async (data: User) => {
		console.log('onSuccess', data);
		updateRolesDialogIsOpen = false;
	}}
/>