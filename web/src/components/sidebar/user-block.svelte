<script lang="ts">
	import * as Avatar from '$ui/avatar';
	import { generateAvatar, getInitials } from '$utils/avatar';
	import { authStore } from '$stores/auth';

	const user = $authStore.user;
	const identifier = $derived(`${user.id}|${user.email}`);
</script>

<Avatar.Root class="size-8 rounded-lg">
	<Avatar.Image src={generateAvatar(identifier)} alt={user.name} />
	<Avatar.Fallback class="rounded-lg">{getInitials(user.name)}</Avatar.Fallback>
</Avatar.Root>
<div class="grid flex-1 text-left text-sm leading-tight">
	<span class="truncate font-medium">{user.name}</span>
	<span class="truncate text-xs">{user.email}</span>
</div>
