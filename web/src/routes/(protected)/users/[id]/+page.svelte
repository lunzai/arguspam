<script lang="ts">
	import type { UserResource } from '$lib/resources/user.js';
	import type { User } from '$models/user';
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2, MailCheck, ShieldOff, ShieldCheck, ShieldAlert } from '@lucide/svelte';
	import { Separator } from '$ui/separator';
	import * as DL from '$components/description-list';
	import { relativeDateTime } from '$utils/date';
	import { StatusBadge, RedBadge, GreenBadge, YellowBadge } from '$components/badge';
	import type { ResourceItem } from '$resources/api';
	import type { Org } from '$models/org';
	import { Badge } from '$ui/badge';
	import type { UserGroup } from '$models/user-group';
	import type { Role } from '$models/role';

	let { data } = $props();
	const modelResource = $derived(data.user as UserResource);
	const model = $derived(modelResource.data.attributes as User);
	const orgs = $derived(modelResource.data.relationships?.orgs as ResourceItem<Org>[]);
	const userGroups = $derived(
		modelResource.data.relationships?.userGroups as ResourceItem<UserGroup>[]
	);
	const roles = $derived(modelResource.data.relationships?.roles as ResourceItem<Role>[]);
	const modelName = 'users';
	const modelTitle = 'User';
</script>

<h1 class="text-2xl font-medium">{modelTitle} - #{model.id} - {model.name}</h1>
<Card.Root class="gap-3 rounded-lg py-3 shadow-none">
	<Card.Header class="flex items-center justify-between px-3">
		<Card.Title class="text-lg">{modelTitle} Details</Card.Title>
		<Card.Action>
			<Button
				variant="outline"
				class="transition-all duration-200 hover:bg-blue-50 hover:text-blue-500"
				href={`/${modelName}/${model.id}/edit`}
			>
				<Pencil class="h-4 w-4" />
				Edit
			</Button>
			<Button
				variant="outline"
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
						{#each roles as role}
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
