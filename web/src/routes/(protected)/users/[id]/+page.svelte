<script lang="ts">
	import type { UserResource } from '$lib/resources/user.js';
	import type { User } from '$models/user';
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2, MailCheck, ShieldOff, ShieldCheck, ShieldAlert } from '@lucide/svelte';
	import { Separator } from '$ui/separator';
	import * as DL from '$components/description-list';
	import { shortDateTime, relativeDateTime } from '$utils/date';
	import { StatusBadge, RedBadge, GreenBadge, YellowBadge } from '$components/badge';

    let { data } = $props();
    const userResource = data.user as UserResource;
	const user = userResource.data.attributes as User;
	console.log(user);
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
								<GreenBadge>
									<ShieldCheck class="w-4 h-4" />
									Enrolled
								</GreenBadge>
							{:else}
								<YellowBadge>
									<ShieldAlert class="w-4 h-4" />
									Pending Enrollment
								</YellowBadge>
							{/if}
						{:else}
							<RedBadge>
								<ShieldOff class="w-4 h-4" />
								Not Enabled
							</RedBadge>
						{/if}
					</div>
				</DL.Content>
			</DL.Row>
			<DL.Row>
				<DL.Label>Status</DL.Label>
				<DL.Content>
					<StatusBadge status={user.status} />
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