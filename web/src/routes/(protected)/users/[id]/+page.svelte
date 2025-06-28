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
    const userResource = $derived(data.user as UserResource);
	const user = $derived(userResource.data.attributes as User);
	const orgs = $derived(userResource.data.relationships?.orgs as ResourceItem<Org>[]);
	const userGroups = $derived(userResource.data.relationships?.userGroups as ResourceItem<UserGroup>[]);
	const roles = $derived(userResource.data.relationships?.roles as ResourceItem<Role>[]);
</script>

<h1 class="text-2xl font-medium">User - #{user.id} - {user.name}</h1>
<Card.Root class="rounded-lg shadow-none py-3 gap-3">
	<Card.Header class="px-3 flex items-center justify-between">
		<Card.Title class="text-lg">
			User Details
		</Card.Title>
		<Card.Action>
			<Button variant="outline" class="hover:bg-blue-50 hover:text-blue-500 transition-all duration-200" href={`/users/${user.id}/edit`}>
				<Pencil class="w-4 h-4" />
				Edit
			</Button>
			<Button variant="outline" class="text-destructive border-red-200 hover:bg-red-50 hover:text-red-500 transition-all duration-200" href={`/users/${user.id}/delete`}>
				<Trash2 class="w-4 h-4" />
				Delete
			</Button>
		</Card.Action>
		
	</Card.Header>
	<Separator />
	<Card.Content class="px-3 gap-3">
		<DL.Root divider={null}>
			<DL.Row>
				<DL.Label>ID</DL.Label>
				<DL.Content>{user.id}</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Name</DL.Label>
				<DL.Content>{user.name}</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Email</DL.Label>
				<DL.Content>
					<div class="flex items-center gap-2">
						{user.email}
						{#if user.email_verified_at}
							<GreenBadge>
								<MailCheck class="w-4 h-4" />
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
						{#if user.two_factor_enabled}
							{#if user.two_factor_confirmed_at}
								<GreenBadge class="text-sm">
									<ShieldCheck class="w-4 h-4" />
									Enrolled
								</GreenBadge>
							{:else}
								<YellowBadge class="text-sm">
									<ShieldAlert class="w-4 h-4" />
									Pending Enrollment
								</YellowBadge>
							{/if}
						{:else}
							<RedBadge class="text-sm">
								<ShieldOff class="w-4 h-4" />
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
					<StatusBadge status={user.status} class="text-sm" />
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Last Login</DL.Label>
				<DL.Content>
					{relativeDateTime(user.last_login_at)}
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Created At</DL.Label>
				<DL.Content>
					{relativeDateTime(user.created_at)}
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Updated At</DL.Label>
				<DL.Content>
					{relativeDateTime(user.updated_at)}
				</DL.Content>
			</DL.Row>
		</DL.Root>

	</Card.Content>
</Card.Root>