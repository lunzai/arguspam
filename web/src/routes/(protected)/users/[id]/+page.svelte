<script lang="ts">
	import type { ApiUserResource } from '$resources/user.js';
	import type { User } from '$models/user';
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import {
		MailCheck,
		ShieldOff,
		ShieldCheck,
		ShieldAlert,
		MoreHorizontal,     
		UserRoundX,
		UserRoundPen,
		ContactRound
	} from '@lucide/svelte';
	import * as DL from '$components/description-list';
	import { relativeDateTime } from '$utils/date';
	import { StatusBadge, RedBadge, GreenBadge, YellowBadge } from '$components/badge';
	import { Badge } from '$ui/badge';
	import * as DropdownMenu from '$ui/dropdown-menu';
	import FormDialog from './form-dialog.svelte';
	import RoleFormDialog from './role-form-dialog.svelte';
	import type { OrgResource } from '$resources/org';
    import type { RoleResource } from '$resources/role';
    import type { UserGroupResource } from '$resources/user-group';

	let { data } = $props();
	const modelResource = $derived(data.model as ApiUserResource);
	const model = $derived(modelResource.data.attributes as User);
	const orgs = $derived(modelResource.data.relationships?.orgs as OrgResource[]);
	const roles = $derived(data.roles as RoleResource[]);
	const userGroups = $derived(
		modelResource.data.relationships?.userGroups as UserGroupResource[] 
	);
	const userRoles = $derived(modelResource.data.relationships?.roles as RoleResource[]);
	let editUserDialogIsOpen = $state(false);
	let updateRolesDialogIsOpen = $state(false);
</script>

<Card.Root class="w-full">
	<Card.Header>
		<Card.Title class="text-lg">Profile</Card.Title>
		<Card.Description>View user profile.</Card.Description>
		<Card.Action>
			<DropdownMenu.Root>
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
						<DropdownMenu.Item onclick={() => (editUserDialogIsOpen = true)}>
							<UserRoundPen class="h-4 w-4" />
							Edit User
						</DropdownMenu.Item>
						<DropdownMenu.Item onclick={() => (updateRolesDialogIsOpen = true)}>
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
							<UserRoundX class="text-destructive h-4 w-4" />
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
	{model}
	data={data.form}
	onSuccess={async (data: User) => {
		editUserDialogIsOpen = false;
	}}
/>

<RoleFormDialog
	bind:isOpen={updateRolesDialogIsOpen}
	{roles}
	bind:data={data.updateRolesForm}
	onSuccess={async (data: User) => {
		updateRolesDialogIsOpen = false;
	}}
/>
