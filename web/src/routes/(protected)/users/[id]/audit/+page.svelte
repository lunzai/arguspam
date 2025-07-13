<script lang="ts">
	import type { UserResource } from '$lib/resources/user.js';
	import type { User } from '$models/user';
	import * as Card from '$ui/card';
	import { Button } from '$ui/button';
	import { Pencil, Trash2, MailCheck, ShieldOff, ShieldCheck, ShieldAlert, MoreHorizontal, SquareAsterisk } from '@lucide/svelte';
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
	// const modelResource = $derived(data.model as UserResource);
	// const model = $derived(modelResource.data.attributes as User);
	// const orgs = $derived(modelResource.data.relationships?.orgs as ResourceItem<Org>[]);
	// const userGroups = $derived(
	// 	modelResource.data.relationships?.userGroups as ResourceItem<UserGroup>[]
	// );
	// const roles = $derived(modelResource.data.relationships?.roles as ResourceItem<Role>[]);
	// const modelName = 'users';
	// const modelTitle = 'User';
</script>

<!-- <h1 class="text-2xl font-medium">{modelTitle} - #{model.id} - {model.name}</h1> -->

	
<Card.Root class="w-full">
	<Card.Header>
	 	<Card.Title class="text-lg">Aduit</Card.Title>
		<Card.Description>View user audit log.</Card.Description>
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
	<Card.Content>
		Audit
	</Card.Content>
</Card.Root>