<script lang="ts">
	import * as Avatar from '$ui/avatar';
	import { generateAvatar, getInitials } from '$utils/avatar';
	import { layoutStore } from '$stores/layout';

	const user = $derived($layoutStore.user);
	const identifier = $derived(`${user.id}|${user.email}`);
</script>

<Avatar.Root class="size-8 rounded-lg">
	<Avatar.Image src={generateAvatar(identifier)} alt={user.name} />
	<Avatar.Fallback class="rounded-lg">{getInitials(user.name)}</Avatar.Fallback>
</Avatar.Root>
<div class="grid flex-1 text-left">
	<span class="truncate text-sm font-bold">{user.name}</span>
	<span class="truncate text-[10px] text-slate-500 tracking-tight">{user.email}</span>
</div>
