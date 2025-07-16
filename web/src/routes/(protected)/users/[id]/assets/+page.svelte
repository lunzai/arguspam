<script lang="ts">
	import type { UserResource } from '$lib/resources/user.js';
	import type { User } from '$models/user';
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import {
		Pencil,
		Trash2,
		MailCheck,
		ShieldOff,
		ShieldCheck,
		ShieldAlert,
		MoreHorizontal,
		SquareAsterisk
	} from '@lucide/svelte';
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

	let { data } = $props();
</script>

<Card.Root class="w-full">
	<Card.Header>
		<Card.Title class="text-lg">Assets</Card.Title>
		<Card.Description>Manage user assets.</Card.Description>
		<Card.Action>
			<Button variant="outline">
				<Pencil class="h-4 w-4" />
				Edit
			</Button>
			<Button variant="outline" class="text-destructive">
				<Trash2 class="h-4 w-4" />
				Delete User
			</Button>
		</Card.Action>
	</Card.Header>
	<Card.Content>Assets</Card.Content>
</Card.Root>
